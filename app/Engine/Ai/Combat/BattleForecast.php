<?php

namespace App\Engine\Ai\Combat;

use App\Engine\Ai\Economy\ResourceValue;
use App\Engine\Ai\Fleet\FleetComposition;
use App\Engine\Ai\Fleet\FlightPlan;
use App\Engine\Ai\Runtime\RunContext;
use App\Engine\Battle\Battle;
use App\Engine\Entity\Model\FleetEntityCollection;
use App\Engine\Entity\Ship;
use App\Models\Fleet;
use App\Models\Planet;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class BattleForecast
{
	public function __construct(private RunContext $context)
	{
	}

	/** @return array{loss: float, capacity: int}|null */
	public function evaluate(
		Planet $origin,
		Planet $target,
		FlightPlan $flight,
		array $report,
		float $lossLimit,
	): ?array
	{
		$age = max(0, now()->timestamp - $report['date']) + $flight->duration;
		$riskHours = ceil($age / 900) / 4;
		$margin = min(
			(float) config('ai.combat_margin_max', 1.75),
			(float) config('ai.combat_margin', 1.15)
				+ $riskHours * (float) config('ai.combat_margin_per_hour', 0.05),
		);
		$defenders = array_map(fn(int $count) => (int) ceil($count * $margin), $report['units']);
		if (!$this->withinUnitLimit($origin->user_id, array_sum($flight->ships) + array_sum($defenders))) {
			return null;
		}

		$defender = $this->opponent($target->user_id, $origin->user, $report);
		$signature = $this->signature($origin);
		unset($signature['units']);
		$key = $this->cacheKey([
			'raid',
			$signature,
			$target->user_id,
			$flight->ships,
			$defenders,
			$defender->technologies->toArray(),
			$lossLimit,
		]);
		$cached = Cache::get($key);

		if ($cached !== null) {
			$this->context->decision($origin->user_id, 'forecast_cached');

			return empty($cached) ? null : $cached;
		}

		$maxLoss = 0.0;
		$minCapacity = PHP_INT_MAX;
		$runs = 2;

		for ($i = 0; $i < $runs; $i++) {
			if (!$this->context->useSimulation($origin->user_id)) {
				return null;
			}

			$battle = new Battle();
			$battle->addAttackerFleet($this->makeFleet(1, $origin, $origin->user, $flight->ships));
			$battle->addDefenderFleet($this->makeFleet(2, $target, $defender, $defenders));
			$result = $battle->run();

			if (!$result->attackerHasWin()) {
				$this->remember($key, []);

				return null;
			}

			$survivors = $result->getAttackersResultUnits()->getPlayer($origin->user_id)?->getFleet(1);
			$loss = 0.0;
			$capacity = 0;

			foreach ($flight->ships as $id => $count) {
				$remaining = $survivors?->getUnit($id)?->getCount() ?? 0;
				$entity = Ship::createEntity($id, 1, $origin);
				$loss += ($count - $remaining) * ResourceValue::sum($entity->getPrice());
				$capacity += $remaining * $entity->getStorage();
			}

			$maxLoss = max($maxLoss, $loss);
			$minCapacity = min($minCapacity, $capacity);

			if ($maxLoss > $lossLimit) {
				$this->remember($key, []);

				return null;
			}

			// Дополнительные прогоны нужны только у границы допустимых потерь.
			if ($maxLoss > $lossLimit * 0.5) {
				$runs = 4;
			}
		}

		$forecast = ['loss' => $maxLoss, 'capacity' => $minCapacity];
		$this->remember($key, $forecast);

		return $forecast;
	}

	public function canDefend(Planet $planet, array $attackers): bool
	{
		$units = array_sum(array_map(fn(array $ships) => array_sum($ships), $attackers));
		$units += $planet->entities->whereBetween('entity_id', [200, 499])->sum('amount');

		if (!$this->withinUnitLimit($planet->user_id, $units)) {
			return false;
		}

		$key = $this->cacheKey(['defense', $this->signature($planet), $attackers]);
		$cached = Cache::get($key);

		if ($cached !== null) {
			return $cached['safe'];
		}

		for ($i = 0; $i < 2; $i++) {
			if (!$this->context->useSimulation($planet->user_id)) {
				return false;
			}

			$battle = new Battle();

			foreach ($attackers as $userId => $ships) {
				$battle->addAttackerFleet($this->makeFleet($userId, $planet, $this->opponent($userId, $planet->user), $ships));
			}

			$battle->addPlanet($planet);
			$result = $battle->run();

			if ($result->attackerHasWin()) {
				$this->remember($key, ['safe' => false]);

				return false;
			}

			// Остаёмся только при сохранении всего подвижного флота.
			$survivors = $result->getDefendersResultUnits()->getPlayer($planet->user_id)?->getFleet(0);

			foreach (new FleetComposition($planet)->available() as $id => $count) {
				if (($survivors?->getUnit($id)?->getCount() ?? 0) < $count) {
					$this->remember($key, ['safe' => false]);

					return false;
				}
			}
		}

		$this->remember($key, ['safe' => true]);

		return true;
	}

	public function signature(Planet $planet): array
	{
		$user = $planet->user;

		return [
			'user_id' => $user->id,
			'units' => $planet->entities->pluck('amount', 'entity_id')->sortKeys()->all(),
			'technologies' => $user->technologies->pluck('level', 'id')
				->filter()
				->sortKeys()
				->all(),
			'race' => $user->race,
			'mercenary' => $user->officier_mercenary?->isFuture() ?? false,
			'engineer' => $user->officier_engineer?->isFuture() ?? false,
			'fleet_price' => $user->bonus('res_fleet'),
		];
	}

	private function opponent(int $userId, User $observer, ?array $report = null): User
	{
		$user = new User(['id' => $userId, 'username' => 'Opponent']);

		foreach ([109, 110, 111, 120, 121, 122] as $id) {
			$level = ($report['technologies_known'] ?? false) ? ($report['technologies'][$id] ?? 0) : $observer->getTechLevel($id) + 2;
			$user->technologies->getByEntityId($id)->setLevel($level);
		}

		return $user;
	}

	private function withinUnitLimit(int $userId, int $units): bool
	{
		if ($units > (int) config('ai.simulation_unit_limit', 50000)) {
			$this->context->decision($userId, 'simulation_unit_limit');

			return false;
		}

		return true;
	}

	private function cacheKey(array $data): string
	{
		return 'ai:forecast:v2:' . hash('sha256', serialize([$data, config('game.combat')]));
	}

	private function remember(string $key, array $result): void
	{
		Cache::put($key, $result, now()->addSeconds((int) config('ai.forecast_cache_seconds', 600)));
	}

	private function makeFleet(
		int $id,
		Planet $planet,
		User $user,
		array $ships,
	): Fleet
	{
		$fleet = new Fleet([
			'id' => $id,
			'entities' => FleetEntityCollection::createFromArray($ships),
			'start_galaxy' => $planet->galaxy,
			'start_system' => $planet->system,
			'start_planet' => $planet->planet,
			'end_galaxy' => $planet->galaxy,
			'end_system' => $planet->system,
			'end_planet' => $planet->planet,
		]);
		$fleet->setRelation('user', $user);

		return $fleet;
	}
}
