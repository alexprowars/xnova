<?php

namespace App\Engine\Ai\Economy;

use App\Engine\Ai\Fleet\FlightPlan;
use App\Engine\Ai\Runtime\RunContext;
use App\Engine\Entity\Ship;
use App\Engine\Fleet\MissionType;
use App\Models\Planet;
use Illuminate\Database\Eloquent\Collection;

class ColonySupply
{
	public function __construct(private Planet $origin, private RunContext $context, private array $requests)
	{
	}

	/** @param Collection<int, Planet> $colonies */
	public function choose(Collection $colonies): ?array
	{
		$colonies->loadMissing('entities');
		$best = null;
		$reserve = $this->request($this->origin);

		foreach ($colonies as $colony) {
			if ($colony->id === $this->origin->id) {
				continue;
			}

			$colony->setRelation('user', $this->origin->user);
			$needed = $this->request($colony);

			if (empty($needed)) {
				continue;
			}

			$incoming = $this->incomingResources($colony);
			$colony->getProduction()->reset();
			$production = $colony->getProduction()->getResourceProduction();
			$resources = [];

			foreach (ResourceValue::WEIGHTS as $resource => $weight) {
				$available = $colony->{$resource};
				$missing = max(0, ($needed[$resource] ?? 0) - $available - $incoming[$resource]);
				$surplus = max(0, $this->origin->{$resource} - ($reserve[$resource] ?? 0));
				$resources[$resource] = (int) floor(min($missing, $surplus * (float) config('ai.supply_surplus_fraction', 0.5)));
			}

			$ships = $this->transports(array_sum($resources));

			if (empty($ships)) {
				continue;
			}

			$flight = new FlightPlan($this->origin, $colony->coordinates, $ships);

			if (!$flight->isAffordable($this->origin, MissionType::Transport)) {
				continue;
			}

			$capacity = max(0, $flight->capacity);

			foreach (['deuterium', 'crystal', 'metal'] as $resource) {
				// Добыча до прибытия тоже закрывает часть заявки.
				$missing = max(
					0,
					($needed[$resource] ?? 0) - $colony->{$resource} - $incoming[$resource]
						- max(0, $production->get($resource)) * $flight->duration / 3600,
				);
				$resources[$resource] = min($resources[$resource], (int) floor($missing));

				if ($resource === 'deuterium') {
					$resources[$resource] = min(
						$resources[$resource],
						max(
							0,
							(int) floor($this->origin->deuterium - ($reserve[$resource] ?? 0) - $flight->fuel),
						),
					);
				}

				$resources[$resource] = min($resources[$resource], $capacity);
				$capacity -= $resources[$resource];
			}

			$value = ResourceValue::sum($resources) - ResourceValue::sum(['deuterium' => $flight->fuel]);
			$score = $value / max(60, $flight->duration * 2);

			if ($value >= (float) config('ai.min_supply_value', 1000)
				&& ($best === null || $score > $best['score'])) {
				$best = [
					'flight' => $flight,
					'resources' => $resources,
					'score' => $score,
					'planet' => $colony,
				];
			}
		}

		return $best;
	}

	public function canSend(array $plan): bool
	{
		$reserve = $this->request($this->origin);
		$flight = new FlightPlan($this->origin, $plan['flight']->target, $plan['flight']->ships);

		foreach ($plan['resources'] as $resource => $amount) {
			$required = $amount + ($resource === 'deuterium' ? $flight->fuel : 0);
			$available = max(0, $this->origin->{$resource} - ($reserve[$resource] ?? 0));

			if ($available < $required) {
				return false;
			}
		}

		return true;
	}

	private function request(Planet $planet): array
	{
		$request = $this->requests[$planet->id] ?? null;

		if (!$request || $request['expires_at'] <= now()->timestamp) {
			return [];
		}

		$level = $request['category'] === 'tech' ? $planet->user->getTechLevel($request['id']) : $planet->getLevel($request['id']);

		if ($level !== $request['level']) {
			return [];
		}

		return $request['resources'];
	}

	private function incomingResources(Planet $planet): array
	{
		$resources = array_fill_keys(array_keys(ResourceValue::WEIGHTS), 0);

		foreach ($this->context->fleets($this->origin->user_id) as $fleet) {
			if ($fleet->mess != 0
				|| !in_array($fleet->mission, [MissionType::Transport, MissionType::Stay], true)
				|| !$fleet->getDestinationCoordinates()->isSame($planet->coordinates)) {
				continue;
			}

			foreach ($resources as $resource => $amount) {
				$resources[$resource] += $fleet->{'resource_' . $resource};
			}
		}

		return $resources;
	}

	private function transports(int $amount): array
	{
		$ships = [];
		$remaining = (int) ceil($amount * 1.05);

		foreach ([203, 202] as $id) {
			$capacity = max(1, Ship::createEntity($id, 1, $this->origin)->getStorage());
			$count = min($this->origin->getLevel($id), max(0, (int) ceil($remaining / $capacity)));

			if ($count > 0) {
				$ships[$id] = $count;
				$remaining -= $count * $capacity;
			}
		}

		return $ships;
	}
}
