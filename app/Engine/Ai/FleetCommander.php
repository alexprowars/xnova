<?php

namespace App\Engine\Ai;

use App\Engine\Coordinates;
use App\Engine\Entity\Ship;
use App\Engine\Enums\FleetDirection;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\PlanetType;
use App\Engine\Fleet\FleetCollection;
use App\Engine\Fleet\FleetSend;
use App\Engine\Fleet\MissionType;
use App\Engine\Game;
use App\Engine\Objects\ShipObject;
use App\Exceptions\Exception;
use App\Facades\Galaxy;
use App\Facades\Vars;
use App\Models\Ai;
use App\Models\Fleet;
use App\Models\LogsFleet;
use App\Models\Planet;
use App\Models\Statistic;
use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class FleetCommander
{
	private array $reports = [];
	private array $targetWeights = [];
	private StrategyType $strategy;
	private array $state;

	/** @param Collection<int, Planet>|null $colonies */
	public function __construct(private Planet $planet, private Ai $ai, private RunContext $context = new RunContext(), private ?Collection $colonies = null)
	{
		$this->strategy = $ai->strategy;
		$this->state = $ai->state ?? [];
	}

	public function getState(): array
	{
		return $this->state;
	}

	/** @return Collection<int, Planet> */
	private function colonies(): Collection
	{
		$this->colonies ??= $this->planet->user->planets()->whereNull('destroyed_at')
			->where('planet_type', PlanetType::PLANET)->orderBy('id')->get();

		return $this->colonies->filter(fn(Planet $colony) => $colony->user_id === $this->ai->user_id
			&& !$colony->trashed() && !$colony->destroyed_at && $colony->planet_type === PlanetType::PLANET);
	}

	/** @return Builder<Fleet> */
	public static function incomingThreats(): Builder
	{
		return Fleet::query()->where('mess', 0)
			->whereIn('mission', [MissionType::Attack, MissionType::Assault, MissionType::Destruction])
			->where('start_date', '<=', now()->addMinutes(10));
	}

	public function defend(): bool
	{
		return $this->hasSlot() && $this->evacuate();
	}

	public function run(): void
	{
		foreach ($this->state['targets'] ?? [] as $id => $target) {
			if (max($target['scouted_at'] ?? 0, $target['attacked_at'] ?? 0) < now()->subDays(2)->timestamp) {
				unset($this->state['targets'][$id]);
			}
		}

		if (!$this->hasSlot() || (empty($this->attackShips()) && $this->planet->getLevel(208) < 1 && $this->planet->getLevel(209) < 1 && $this->planet->getLevel(210) < 1)) {
			return;
		}

		if ($this->evacuate()) {
			return;
		}

		$this->colonize();

		if (!$this->hasSlot()) {
			return;
		}

		$this->reports = $this->context->reports($this->planet->user_id);
		$targets = $this->targets();
		$this->attack($targets);

		if ($this->hasSlot()) {
			$this->scout($targets);
		}

		if ($this->hasSlot()) {
			$this->recycle();
		}

		if ($this->hasSlot()) {
			$this->supplyColony();
		}
	}

	private function hasSlot(): bool
	{
		$max = 1 + $this->planet->user->getTechLevel('computer');

		if ($this->planet->user->officier_admiral?->isFuture()) {
			$max += 2;
		}

		return Fleet::query()->whereBelongsTo($this->planet->user)->count() < $max;
	}

	private function flightExists(Coordinates $target, array $missions): bool
	{
		return Fleet::query()->whereBelongsTo($this->planet->user)
			->coordinates(FleetDirection::END, $target)->whereIn('mission', $missions)->where('mess', 0)->exists();
	}

	/** @return array{duration: int, fuel: int, capacity: int} */
	private function flight(array $ships, Coordinates $target): array
	{
		$collection = FleetCollection::createFromArray($ships, $this->planet);
		$distance = $collection->getDistance($this->planet->coordinates, $target);
		$duration = $collection->getDuration(10, $distance);
		$fuel = $collection->getConsumption($duration, $distance);

		// Отрицательный остаток означает, что даже топливо не помещается в трюм.
		return ['duration' => $duration, 'fuel' => $fuel, 'capacity' => $collection->getStorage() - $fuel];
	}

	private function maxFlightDuration(MissionType $mission): float
	{
		$maxFlightHours = $mission === MissionType::Colonization
			? (float) config('ai.max_colonization_flight_hours', 48)
			: (float) config('ai.max_flight_hours', 4);

		return $maxFlightHours * 3600 / Game::getSpeed('fleet');
	}

	private function send(Coordinates $target, MissionType $mission, array $ships, array $resources = [], ?Closure $validate = null, ?Closure $onSent = null): bool
	{
		if (empty($ships)) {
			return false;
		}

		$origin = $this->planet->coordinates;

		try {
			$fleet = $this->planet->getConnection()->transaction(function () use ($origin, $target, $mission, $ships, $resources, $validate, $onSent): ?Fleet {
				$user = User::query()->lockForUpdate()->find($this->ai->user_id);
				$this->planet->refreshForUpdate();

				if (!$user || $user->isVacation() || $user->blocked_at || $this->planet->trashed() || $this->planet->destroyed_at
					|| $this->planet->user_id !== $user->id || !$origin->isSame($this->planet->coordinates)) {
					return null;
				}

				$this->planet->setRelation('user', $user);
				$this->planet->setRelation('entities', $this->planet->entities()->lockForUpdate()->get());
				$this->planet->getProduction()->reset();

				if (!$this->hasSlot() || !$this->missionIsCurrent($target, $mission) || ($validate && !$validate())) {
					return null;
				}

				$flight = $this->flight($ships, $target);

				if ($flight['capacity'] < array_sum($resources) || $flight['fuel'] + ($resources['deuterium'] ?? 0) > $this->planet->deuterium
					|| $flight['duration'] > $this->maxFlightDuration($mission)) {
					return null;
				}

				$sender = new FleetSend($this->planet, $target, $mission);
				$sender->setFleets($ships);
				$sender->setResources($resources);
				$fleet = $sender->send();

				if ($onSent) {
					$onSent();
				}

				// Память о миссии фиксируется вместе с флотом, даже если ход прервётся позже.
				$this->ai->update(['state' => $this->state]);
				$this->planet->getConnection()->afterCommit(fn() => $this->context->recordFleet($fleet));

				return $fleet;
			});

			if (!$fleet) {
				return false;
			}

			if (config('ai.log_decisions', true)) {
				Log::info('ai.fleet', ['user_id' => $this->planet->user_id, 'planet_id' => $this->planet->id, 'fleet_id' => $fleet->id, 'mission' => $mission->name, 'target' => $target->toArray()]);
			}

			return true;
		} catch (Exception $exception) {
			// Проверка миссии могла отклонить полёт после изменения игрового состояния.
			$this->planet->refresh();
			$this->planet->load('entities', 'user');
			$this->planet->getProduction()->reset();
			Log::debug('ai.fleet_rejected', ['user_id' => $this->planet->user_id, 'mission' => $mission->name, 'reason' => $exception->getMessage()]);

			return false;
		}
	}

	private function missionIsCurrent(Coordinates $target, MissionType $mission): bool
	{
		$missions = match ($mission) {
			MissionType::Attack => [MissionType::Attack, MissionType::Assault],
			MissionType::Spy => [MissionType::Spy, MissionType::Attack],
			MissionType::Transport => [MissionType::Transport, MissionType::Stay],
			default => [$mission],
		};

		if ($mission !== MissionType::Stay && $this->flightExists($target, $missions)) {
			return false;
		}

		if ($mission === MissionType::Colonization) {
			$user = $this->planet->user;
			$max = min((int) config('game.maxPlanets', 9), $user->getTechLevel('colonization') + 1);

			return $user->planets()->where('planet_type', PlanetType::PLANET)->whereNull('destroyed_at')->count() < $max
				&& !Fleet::query()->whereBelongsTo($user)->where('mission', MissionType::Colonization)->where('mess', 0)->exists()
				&& !Fleet::query()->coordinates(FleetDirection::END, $target)->where('mission', MissionType::Colonization)->where('mess', 0)->exists()
				&& $this->colonizationSystemIsCurrent($target);
		}

		if (in_array($mission, [MissionType::Stay, MissionType::Transport], true)) {
			if (!Planet::query()->coordinates($target)->where('user_id', $this->planet->user_id)->whereNull('destroyed_at')->exists()) {
				return false;
			}
		}

		if ($mission === MissionType::Stay) {
			return self::incomingThreats()->coordinates(FleetDirection::END, $this->planet->coordinates)->whereNot('user_id', $this->planet->user_id)->exists()
				&& !Fleet::query()->coordinates(FleetDirection::END, $target)->where('mess', 0)
					->whereIn('mission', [MissionType::Attack, MissionType::Assault, MissionType::Destruction])->exists();
		}

		return true;
	}

	private function colonizationSystemIsCurrent(Coordinates $target): bool
	{
		// Общая карта — снимок прохода; перед отправкой проверяем только выбранную систему.
		$owners = Planet::query()->whereIn('user_id', Ai::query()->select('user_id'))
			->where('galaxy', $target->getGalaxy())->where('system', $target->getSystem())
			->where('planet_type', PlanetType::PLANET)->whereNull('destroyed_at')->distinct()->pluck('user_id');
		$pendingOwners = Fleet::query()->whereIn('user_id', Ai::query()->select('user_id'))
			->where('end_galaxy', $target->getGalaxy())->where('end_system', $target->getSystem())
			->where('mission', MissionType::Colonization)->where('mess', 0)->distinct()->pluck('user_id');
		$owners = $owners->merge($pendingOwners)->unique();

		return !$owners->contains($this->planet->user_id) && $owners->count() < (int) config('ai.max_bots_per_system', 3);
	}

	private function targetIsCurrent(Planet $target): bool
	{
		if (($this->state['targets'][$target->id]['attacked_at'] ?? 0) > now()->subMinutes((int) config('ai.attack_cooldown_minutes', 180))->timestamp) {
			return false;
		}

		$current = Planet::query()->with('user')->where('user_id', $target->user_id)->whereNull('destroyed_at')
			->coordinates($target->coordinates)->find($target->id);
		$user = $current?->user;

		if (!$user || $user->isVacation() || $user->blocked_at || $user->roles->isNotEmpty()
			|| $user->id === $this->planet->user_id) {
			return false;
		}

		return true;
	}

	private function combatState(): array
	{
		$user = $this->planet->user;

		return [
			'ships' => $this->attackShips(),
			'technologies' => $user->technologies->pluck('level', 'id')->filter()->sortKeys()->all(),
			'race' => $user->race,
			'mercenary' => $user->officier_mercenary?->isFuture() ?? false,
			'admiral' => $user->officier_admiral?->isFuture() ?? false,
		];
	}

	private function colonize(): void
	{
		if ($this->planet->getLevel(208) < 1) {
			return;
		}

		$user = $this->planet->user;
		$max = min((int) config('game.maxPlanets', 9), $user->getTechLevel('colonization') + 1);
		$count = $this->colonies()->count();
		$pending = Fleet::query()->whereBelongsTo($user)->where('mission', MissionType::Colonization)->where('mess', 0)->count();

		if ($count + $pending >= $max || $pending > 0) {
			return;
		}

		$ships = [208 => 1];

		foreach ($this->context->colonization()->candidates($this->planet->user_id) as $position) {
			$flight = $this->flight($ships, $position);

			if ($flight['capacity'] < 0 || $flight['fuel'] > $this->planet->deuterium || $flight['duration'] > $this->maxFlightDuration(MissionType::Colonization)) {
				continue;
			}

			$positions = Galaxy::getFreePositions($position, 4, min(12, (int) config('game.maxPlanetInSystem')));
			shuffle($positions);

			foreach ($positions as $slot) {
				$target = new Coordinates($position->getGalaxy(), $position->getSystem(), $slot, PlanetType::PLANET);

				if (Fleet::query()->coordinates(FleetDirection::END, $target)->where('mission', MissionType::Colonization)->where('mess', 0)->exists()) {
					continue;
				}

				$resources = $this->cargo($flight, 0.2, ['metal' => 5000, 'crystal' => 3000, 'deuterium' => 2000]);

				if ($this->send($target, MissionType::Colonization, $ships, $resources)) {
					return;
				}
			}
		}
	}

	/** @return array<Planet> */
	private function targets(): array
	{
		$radius = (int) config('ai.search_radius', 50);
		$galaxyRadius = (int) config('ai.search_galaxy_radius', 1);
		$targetLimit = (int) config('ai.target_limit', 30);
		$cacheKey = 'ai:targets:v1:' . implode(':', [
			$this->planet->id, $this->planet->user_id, $this->planet->galaxy, $this->planet->system, $this->planet->planet,
			$radius, $galaxyRadius, (int) config('game.maxGalaxyInWorld'), $targetLimit,
		]);

		// Сохраняем итог отбора вместе с данными целей и весами, без повторных запросов при чтении.
		$cached = Cache::remember($cacheKey, now()->addMinutes((int) config('ai.target_cache_minutes', 30)), function () use ($radius, $galaxyRadius, $targetLimit): array {
			return array_map(fn(Planet $target) => [
				'planet' => Arr::only($target->getAttributes(), ['id', 'user_id', 'galaxy', 'system', 'planet', 'planet_type']),
				'user' => ['id' => $target->user_id, 'username' => $target->user->username],
				'weight' => $this->targetWeights[$target->id],
			], $this->selectTargets($radius, $galaxyRadius, $targetLimit));
		});
		$targets = [];
		$this->targetWeights = [];

		foreach ($cached as $item) {
			$target = new Planet()->newFromBuilder($item['planet']);
			$target->setRelation('user', new User()->newFromBuilder($item['user']));
			$this->targetWeights[$target->id] = $item['weight'];
			$targets[] = $target;
		}

		return $targets;
	}

	/** @return array<Planet> */
	private function selectTargets(int $radius, int $galaxyRadius, int $targetLimit): array
	{
		$query = Planet::query()->with(['user'])
			->whereNot('user_id', $this->planet->user_id)
			->where('planet_type', PlanetType::PLANET)->whereNull('destroyed_at')
			->whereHas('user', function (Builder $query) {
				$query->whereNull('vacation')->whereNull('blocked_at')->whereDoesntHave('roles');
			});
		/** @var Collection<int, Planet> $targets */
		$targets = new Collection();
		$firstGalaxy = max(1, $this->planet->galaxy - $galaxyRadius);
		$lastGalaxy = min((int) config('game.maxGalaxyInWorld'), $this->planet->galaxy + $galaxyRadius);

		for ($galaxy = $firstGalaxy; $galaxy <= $lastGalaxy; $galaxy++) {
			$galaxyQuery = (clone $query)->where('galaxy', $galaxy);

			if ($galaxy === $this->planet->galaxy) {
				$galaxyQuery->whereBetween('system', [max(1, $this->planet->system - $radius), $this->planet->system + $radius])
					->orderByRaw('ABS(`system` - ?)', [$this->planet->system])->orderBy('id');
			} else {
				// Межгалактическое расстояние не зависит от номера системы.
				$galaxyQuery->inRandomOrder();
			}

			$targets = $targets->merge($galaxyQuery->limit($targetLimit * 5)->get());
		}

		$bots = $this->context->bots();
		$activeHumansThreshold = (int) config('ai.active_humans_threshold', 10);
		$preferBots = $activeHumansThreshold > 0 && $this->context->activeHumans() < $activeHumansThreshold;
		$points = Statistic::query()->where('stat_type', 1)->where('stat_code', 1)
			->whereIn('user_id', [...$targets->pluck('user_id')->all(), $this->planet->user_id])->pluck('total_points', 'user_id');
		$cappedTargets = LogsFleet::query()->where('s_id', $this->planet->user_id)
			->where('mission', MissionType::Attack)->where('amount', '>=', 3)
			->where('created_at', '>=', now()->startOfDay())
			->get(['e_galaxy', 'e_system', 'e_planet'])
			->mapWithKeys(fn(LogsFleet $log) => [$log->e_galaxy . ':' . $log->e_system . ':' . $log->e_planet => true])->all();
		$eligible = [];
		$distances = [];
		$distanceCalculator = new FleetCollection();

		foreach ($targets as $target) {
			$user = $target->user;

			$key = $target->galaxy . ':' . $target->system . ':' . $target->planet;
			if (isset($cappedTargets[$key])) {
				continue;
			}

			if (config('game.noobprotection') && (int) config('game.noobprotectionPoints') > 0 && $user->onlinetime?->greaterThan(now()->subDays(7))) {
				$theirPoints = $points[$user->id] ?? 0;
				$factor = (int) config('game.noobprotectionFactor');

				if ($theirPoints < (int) config('game.noobprotectionPoints') || ($factor > 0 && ($points[$this->planet->user_id] ?? 0) > $theirPoints * $factor)) {
					continue;
				}
			}

			$memory = $this->state['targets'][$target->id] ?? [];

			if (($memory['attacked_at'] ?? 0) > now()->subMinutes((int) config('ai.attack_cooldown_minutes', 180))->timestamp) {
				continue;
			}

			$this->targetWeights[$target->id] = $preferBots && isset($bots[$user->id]) ? (float) config('ai.bot_target_bonus', 3) : 1.0;
			$distances[$target->id] = $distanceCalculator->getDistance($this->planet->coordinates, $target->coordinates);
			$eligible[] = $target;
		}

		usort($eligible, function (Planet $a, Planet $b) use ($distances) {
			$scoreA = $this->targetWeights[$a->id] / (1 + $distances[$a->id] / 950);
			$scoreB = $this->targetWeights[$b->id] / (1 + $distances[$b->id] / 950);

			return $scoreB <=> $scoreA ?: ($this->state['targets'][$a->id]['scouted_at'] ?? 0) <=> ($this->state['targets'][$b->id]['scouted_at'] ?? 0);
		});

		$byGalaxy = [];

		foreach ($eligible as $target) {
			$byGalaxy[$target->galaxy][] = $target;
		}

		// Ближайшие цели не должны вытеснять соседние галактики из ограниченной выборки.
		$selected = [];

		while (!empty($byGalaxy) && count($selected) < $targetLimit) {
			foreach (array_keys($byGalaxy) as $galaxy) {
				$selected[] = array_shift($byGalaxy[$galaxy]);

				if (empty($byGalaxy[$galaxy])) {
					unset($byGalaxy[$galaxy]);
				}

				if (count($selected) === $targetLimit) {
					break;
				}
			}
		}

		return $selected;
	}


	private function report(Planet $target): ?array
	{
		$key = $target->galaxy . ':' . $target->system . ':' . $target->planet . ':' . $target->planet_type->value;
		$report = $this->reports[$key] ?? null;

		if (!$report || $report['user_id'] !== $target->user_id || $report['date'] <= ($this->state['targets'][$target->id]['attacked_at'] ?? 0)
			|| $report['date'] < now()->subMinutes((int) config('ai.report_lifetime_minutes', 120))->timestamp) {
			return null;
		}

		return $report;
	}

	/** @param array<Planet> $targets */
	private function scout(array $targets): void
	{
		if ($this->planet->getLevel(210) < 1) {
			return;
		}

		foreach ($targets as $target) {
			$memory = $this->state['targets'][$target->id] ?? [];
			$report = $this->report($target);

			if (($report && $report['fleet_known'] && $report['defense_known']) || ($memory['scouted_at'] ?? 0) > now()->subMinutes((int) config('ai.scout_cooldown_minutes', 20))->timestamp || $this->flightExists($target->coordinates, [MissionType::Spy, MissionType::Attack])) {
				continue;
			}

			$count = $report ? max(7, ($memory['probes'] ?? 7) * 2) : max(7, $memory['probes'] ?? 7);
			$required = min($count, (int) config('ai.max_probes', 32));
			$count = min($required, $this->planet->getLevel(210));

			if ($this->send($target->coordinates, MissionType::Spy, [210 => $count], [],
				fn() => $this->targetIsCurrent($target),
				function () use ($target, $count, $required) {
					$this->state['targets'][$target->id] = array_merge($this->state['targets'][$target->id] ?? [], ['scouted_at' => now()->timestamp, 'probes' => $count, 'required_probes' => $required]);
				})) {
				return;
			}
		}
	}

	/** @param array<Planet> $targets */
	private function attack(array $targets): void
	{
		$ships = $this->attackShips();
		$combatState = $this->combatState();

		if (empty($ships)) {
			return;
		}

		$candidates = [];

		foreach ($targets as $target) {
			$report = $this->report($target);

			if (!$report || !$report['fleet_known'] || !$report['defense_known'] || $this->flightExists($target->coordinates, [MissionType::Attack, MissionType::Assault])) {
				continue;
			}

			$memory = $this->state['targets'][$target->id] ?? [];

			if (($memory['evaluated_at'] ?? 0) > now()->subMinutes(10)->timestamp && ($memory['evaluated_report'] ?? 0) === $report['date']) {
				continue;
			}

			$flight = $this->flight($ships, $target->coordinates);
			$loot = array_sum(array_intersect_key($report['resources'], array_flip(['metal', 'crystal', 'deuterium']))) / 2;
			$profit = min($loot, $flight['capacity']) - $flight['fuel'] * 2;

			if ($flight['fuel'] > $this->planet->deuterium * 0.5 || $flight['duration'] > $this->maxFlightDuration(MissionType::Attack) || $profit < (float) config('ai.min_raid_profit', 1000)) {
				continue;
			}

			$candidates[] = ['target' => $target, 'report' => $report, 'flight' => $flight, 'loot' => $loot, 'score' => $profit * $this->targetWeights[$target->id] / max(60, $flight['duration'] * 2)];
		}

		usort($candidates, fn(array $a, array $b) => $b['score'] <=> $a['score']);
		$fleetCost = 0.0;

		foreach ($ships as $id => $count) {
			$price = Ship::createEntity($id, 1, $this->planet)->getPrice();
			$fleetCost += $count * ($price['metal'] + $price['crystal'] * 1.5 + $price['deuterium'] * 2);
		}

		foreach (array_slice($candidates, 0, 3) as $candidate) {
			$target = $candidate['target'];

			try {
				$forecast = new BattleForecast()->evaluate($this->planet, $target, $ships, $candidate['report']);
			} catch (Throwable $exception) {
				Log::warning('ai.forecast_failed', ['user_id' => $this->planet->user_id, 'reason' => $exception->getMessage()]);
				return;
			}

			$this->state['targets'][$target->id]['evaluated_at'] = now()->timestamp;
			$this->state['targets'][$target->id]['evaluated_report'] = $candidate['report']['date'];
			$lossRatio = (float) config('ai.max_loss_ratio', 0.15) * ($this->strategy === StrategyType::ECONOMY ? 0.5 : 1);

			if (!$forecast || $forecast['loss'] > $fleetCost * $lossRatio) {
				continue;
			}

			$profit = min($candidate['loot'], max(0, $forecast['capacity'] - $candidate['flight']['fuel'])) - $forecast['loss'] - $candidate['flight']['fuel'] * 2;

			if ($profit < (float) config('ai.min_raid_profit', 1000)) {
				continue;
			}

			if ($this->send($target->coordinates, MissionType::Attack, $ships, [],
				function () use ($target, $ships, $combatState, $forecast, $candidate): bool {
					if ($combatState !== $this->combatState() || !$this->targetIsCurrent($target) || !$this->report($target)) {
						return false;
					}

					$flight = $this->flight($ships, $target->coordinates);
					$profit = min($candidate['loot'], max(0, $forecast['capacity'] - $flight['fuel'])) - $forecast['loss'] - $flight['fuel'] * 2;

					return $flight['fuel'] <= $this->planet->deuterium * 0.5 && $profit >= (float) config('ai.min_raid_profit', 1000);
				},
				function () use ($target) {
					$this->state['targets'][$target->id]['attacked_at'] = now()->timestamp;
				})) {
				return;
			}

			if ($combatState !== $this->combatState()) {
				unset($this->state['targets'][$target->id]['evaluated_at'], $this->state['targets'][$target->id]['evaluated_report']);
				return;
			}
		}
	}

	/** @return array<int, int> */
	private function attackShips(): array
	{
		$ships = [];

		foreach (Vars::getObjectsByType(ItemType::FLEET) as $object) {
			$id = $object->getId();

			if (!$object instanceof ShipObject || $object->getSpeed() <= 0 || in_array($id, [208, 209, 210, 216], true)) {
				continue;
			}

			$count = $this->planet->getLevel($id);

			if ($count > 0) {
				$ships[$id] = $count;
			}
		}

		return $ships;
	}

	private function recycle(): void
	{
		$count = $this->planet->getLevel(209);

		if ($count < 1) {
			return;
		}

		$targets = Planet::query()->where('galaxy', $this->planet->galaxy)->where('planet_type', PlanetType::PLANET)
			->whereBetween('system', [$this->planet->system - 10, $this->planet->system + 10])
			->where(function (Builder $query) {
				$query->where('debris_metal', '>', 0)->orWhere('debris_crystal', '>', 0);
			})->orderByRaw('ABS(`system` - ?)', [$this->planet->system])->limit(10)->get();

		foreach ($targets as $target) {
			$coordinates = new Coordinates($target->galaxy, $target->system, $target->planet, PlanetType::DEBRIS);

			if ($this->flightExists($coordinates, [MissionType::Recycling])) {
				continue;
			}

			$debris = $target->debris_metal + $target->debris_crystal;
			$ships = [209 => min($count, max(1, (int) ceil($debris / max(1, Ship::createEntity(209, 1, $this->planet)->getStorage()))))];
			$flight = $this->flight($ships, $coordinates);

			if (min($debris, $flight['capacity']) > $flight['fuel'] * 2 + (float) config('ai.min_raid_profit', 1000) && $this->send($coordinates, MissionType::Recycling, $ships)) {
				return;
			}
		}
	}

	private function evacuate(): bool
	{
		$threat = self::incomingThreats()->coordinates(FleetDirection::END, $this->planet->coordinates)
			->whereNot('user_id', $this->planet->user_id)->exists();

		if (!$threat) {
			return false;
		}

		$ships = [];

		foreach (Vars::getObjectsByType(ItemType::FLEET) as $object) {
			if ($object instanceof ShipObject && $object->getSpeed() > 0 && $this->planet->getLevel($object) > 0) {
				$ships[$object->getId()] = $this->planet->getLevel($object);
			}
		}

		if (empty($ships)) {
			return false;
		}

		$destinations = $this->colonies()->where('id', '!=', $this->planet->id)
			->sortBy(fn(Planet $colony) => [abs($colony->galaxy - $this->planet->galaxy), abs($colony->system - $this->planet->system)]);

		foreach ($destinations as $destination) {
			if (Fleet::query()->coordinates(FleetDirection::END, $destination->coordinates)->where('mess', 0)
				->whereIn('mission', [MissionType::Attack, MissionType::Assault, MissionType::Destruction])->exists()) {
				continue;
			}

			$flight = $this->flight($ships, $destination->coordinates);

			if ($this->send($destination->coordinates, MissionType::Stay, $ships, $this->cargo($flight, 1.0))) {
				return true;
			}
		}

		return false;
	}

	private function supplyColony(): void
	{
		if ($this->planet->getLevel(1) < 12) {
			return;
		}

		$ships = [];

		foreach ([202, 203] as $id) {
			if ($this->planet->getLevel($id) > 0) {
				$ships[$id] = min(5, $this->planet->getLevel($id));
			}
		}

		if (empty($ships)) {
			return;
		}

		$colonies = $this->colonies()->where('id', '!=', $this->planet->id)->sortByDesc('id');
		$colonies->loadMissing('entities');

		foreach ($colonies as $colony) {
			if ($colony->getLevel(1) >= 10 || $this->flightExists($colony->coordinates, [MissionType::Transport, MissionType::Stay])) {
				continue;
			}

			$flight = $this->flight($ships, $colony->coordinates);
			$resources = $this->cargo($flight, 0.1, ['metal' => max(0, 10000 - $colony->metal), 'crystal' => max(0, 6000 - $colony->crystal), 'deuterium' => max(0, 3000 - $colony->deuterium)]);

			if (array_sum($resources) >= 2000 && $this->send($colony->coordinates, MissionType::Transport, $ships, $resources)) {
				return;
			}
		}
	}

	private function cargo(array $flight, float $fraction, array $limits = []): array
	{
		$resources = [];
		$capacity = max(0, $flight['capacity']);

		foreach (['deuterium', 'crystal', 'metal'] as $resource) {
			$available = max(0, $this->planet->{$resource} - ($resource === 'deuterium' ? $flight['fuel'] : 0));
			$count = (int) floor(min($capacity, $available * $fraction, $limits[$resource] ?? $available));
			$resources[$resource] = $count;
			$capacity -= $count;
		}

		return $resources;
	}
}
