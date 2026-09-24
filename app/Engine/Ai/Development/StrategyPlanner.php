<?php

namespace App\Engine\Ai\Development;

use App\Engine\Ai\Economy\ResourceValue;
use App\Engine\Building;
use App\Engine\EntityFactory;
use App\Engine\Entity\Entity;
use App\Engine\Enums\FleetDirection;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\PlanetType;
use App\Engine\Enums\QueueType;
use App\Engine\Objects\BaseObject;
use App\Engine\Objects\BuildingObject;
use App\Engine\Objects\ObjectsFactory;
use App\Models\Fleet;
use App\Models\Planet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class StrategyPlanner
{
	private array $recommendations = [];
	private array $units = [];
	private ?array $snapshot = null;

	/** @param Collection<int, Planet>|null $colonies */
	public function __construct(
		private Planet $planet,
		private StrategyType $strategy,
		private bool $researchHub = true,
		private int $probeTarget = 7,
		private ?Collection $colonies = null,
		private array $fleetRequests = [],
	)
	{
	}

	/** @return array<int, array{id: int, count: int, score: float, reason: string}> */
	public function getRecommendations(ItemType $type): array
	{
		$this->prepare();

		$result = array_filter($this->recommendations, fn(array $item) => ObjectsFactory::get($item['id'])->getType() === $type);
		usort($result, fn(array $a, array $b) => $b['score'] <=> $a['score'] ?: $a['id'] <=> $b['id']);

		return $result;
	}

	public function prepare(bool $onlyAvailableQueues = false): void
	{
		if ($this->snapshot === null) {
			$this->snapshot = $this->planningState();

			if (!$onlyAvailableQueues || $this->hasAvailableQueue()) {
				$this->plan();
			}
		}
	}

	public function isCurrent(): bool
	{
		return $this->snapshot !== null && $this->snapshot === $this->planningState();
	}

	/** @return Entity<BaseObject> */
	public function getEntity(int $id): Entity
	{
		$object = ObjectsFactory::get($id);
		$level = $object->getType() === ItemType::TECH ? $this->planet->user->getTechLevel($id) : $this->planet->getLevel($id);

		return EntityFactory::get($id, $level, $this->planet);
	}

	private function hasAvailableQueue(): bool
	{
		$queue = collect($this->snapshot['queue']);
		$localQueue = $queue->where('planet_id', $this->planet->id);

		return (!$localQueue->contains('type', QueueType::BUILDING->value)
			&& $this->planet->field_current < $this->planet->getMaxFields())
			|| !$localQueue->contains('type', QueueType::SHIPYARD->value)
			|| ($this->researchHub && !$queue->contains('type', QueueType::RESEARCH->value));
	}

	private function planningState(): array
	{
		$user = $this->planet->user;
		$officers = [];

		foreach (['geologist', 'engineer', 'admiral', 'architect', 'technocrat', 'metaphysician'] as $officer) {
			$officers[$officer] = $user->{'officier_' . $officer}?->isFuture() ?? false;
		}

		return [
			'entities' => $this->planet->entities->pluck('amount', 'entity_id')->sortKeys()->all(),
			'technologies' => $user->technologies->pluck('level', 'id')
				->filter()
				->sortKeys()
				->all(),
			'race' => $user->race,
			'officers' => $officers,
			'fields' => $this->planet->getMaxFields(),
			'used_fields' => $this->planet->field_current,
			'queue' => $this->queueState(),
			'fleets' => $this->fleetState(),
			'colonization' => $this->colonizationState(),
		];
	}

	private function queueState(): array
	{
		$user = $this->planet->user;

		return $user->queue()
			->where(function (Builder $query) {
				$query->where('planet_id', $this->planet->id)
					->orWhere('type', QueueType::RESEARCH)
					->orWhereIn('object_id', [31, 208]);
			})
			->orderBy('id')
			->toBase()
			->get(['id', 'planet_id', 'object_id', 'level', 'type'])
			->map(fn($row) => (array) $row)
			->all();
	}

	private function fleetState(): array
	{
		$user = $this->planet->user;

		return Fleet::query()
			->whereBelongsTo($user)
			->when(
				!$this->researchHub,
				fn(Builder $query) => $query->coordinates(FleetDirection::START, $this->planet->coordinates),
			)
			->orderBy('id')
			->toBase()
			->get(['id', 'entities', 'start_galaxy', 'start_system', 'start_planet', 'start_type'])
			->map(fn($row) => (array) $row)
			->all();
	}

	private function colonizationState(): array
	{
		$user = $this->planet->user;

		if (!$this->researchHub) {
			return [];
		}

		return $user->planets()
			->whereNull('destroyed_at')
			->select(['id', 'planet_type'])
			->withSum(['entities as colonizers' => fn($query) => $query->where('entity_id', 208)], 'amount')
			->orderBy('id')
			->toBase()
			->get()
			->map(fn($row) => (array) $row)
			->all();
	}

	private function plan(): void
	{
		if ($this->planet->planet_type !== PlanetType::PLANET) {
			return;
		}

		$this->loadUnits();
		$this->planMines();

		foreach ($this->fleetRequests as $id => $count) {
			$this->addGoal($id, $count, 115, 'Fleet required by reconnaissance');
		}

		$metalLevel = $this->planet->getLevel(1);
		$production = $this->planet->getProduction()->getResourceProduction();
		$storage = $this->planet->getProduction()->getStorageCapacity();

		foreach ([
			22 => 'metal',
			23 => 'crystal',
			24 => 'deuterium',
		] as $id => $resource) {
			$free = $storage->get($resource) - $this->planet->{$resource};

			if ($free < max(0, $production->get($resource)) * 2
				|| $this->planet->{$resource} >= $storage->get($resource) * 0.9) {
				$this->addGoal($id, $this->planet->getLevel($id) + 1, 700, 'Storage will be full soon');
			}
		}

		if ($this->planet->getMaxFields() - $this->planet->field_current <= 20) {
			$this->addGoal(33, $this->planet->getLevel(33) + 1, 1100, 'Reserve space for planetary development');
		}

		// Сначала создаём доход, затем открываем инфраструктуру и флот.
		if ($metalLevel < 6 || $this->planet->getLevel(2) < 4 || $this->planet->getLevel(3) < 2) {
			return;
		}

		$colonyCount = 0;
		$hasColonizer = false;

		if ($this->researchHub) {
			$this->colonies ??= $this->planet->user->planets()
				->whereNull('destroyed_at')
				->where('planet_type', PlanetType::PLANET)
				->get();
			$colonies = $this->colonies->filter(
				fn(Planet $colony) => $colony->user_id === $this->planet->user_id
					&& !$colony->trashed()
					&& !$colony->destroyed_at
					&& $colony->planet_type === PlanetType::PLANET,
			);
			$colonies->loadMissing('entities');
			$colonyCount = collect($this->snapshot['colonization'])->where('planet_type', PlanetType::PLANET->value)->count();
			$hasColonizer = collect($this->snapshot['colonization'])->contains(fn(array $planet) => $planet['colonizers'] > 0);

			if ($colonies->contains(fn(Planet $colony) => $colony->getMaxFields() - $colony->field_current <= 20)) {
				$this->addGoal(108, 10, 1100, 'Prepare the nanite factory for colony expansion');
				$this->addGoal(113, 12, 1100, 'Unlock the terraformer for colonies');
			}
		}

		if ($metalLevel >= 10) {
			// Базовая логистика нужна раньше дорогой колонизации и тяжёлого флота.
			// Отдельные небольшие цели не заставляют сначала заполнить весь парк транспортов.
			$this->addGoal(202, 2, 125, 'First cargo ship');
			$this->addGoal(210, 7, 130, 'Initial reconnaissance');
			$this->addGoal(108, 2, 120, 'Initial fleet slots');
			$this->addGoal(204, 5, 115, 'First combat escort');
			$this->addGoal(14, min(4, intdiv($metalLevel, 2)), 105, 'Basic construction speed boost');
		}

		$this->addGoal(14, min(10, max(2, intdiv($metalLevel, 2))), 65, 'Speed up construction');
		$this->addGoal(108, min(12, max(2, intdiv($metalLevel, 3))), 70, 'Fleet slots for reconnaissance and missions');
		$this->addGoal(106, min(16, max(3, intdiv($metalLevel, 2) - 1)), 75, 'Target reconnaissance');

		$scale = max(1, ($metalLevel - 4) ** 2);
		$military = match ($this->strategy) {
			StrategyType::MILITARY => 1.5,
			StrategyType::ECONOMY => 0.5,
			StrategyType::BALANCED => 1.0,
		};

		$this->addGoal(202, max(2, (int) ceil($scale / 5)), 85, 'Cargo ships for resource collection and colonies');
		$this->addGoal(
			210,
			min((int) config('ai.max_probes', 32), max(7, $metalLevel, $this->probeTarget)),
			95,
			'Espionage probes',
		);
		$this->addGoal(204, (int) ceil($scale * $military), 60, 'Strike fleet');
		$this->addGoal(401, max(5, (int) ceil($scale / 3)), 45, 'Basic protection for resource production');

		if ($metalLevel >= 10) {
			$this->addGoal(203, max(2, (int) ceil($scale / 15)), 70, 'Cargo capacity for raids');
			$this->addGoal(205, max(2, (int) ceil($scale * $military / 5)), 65, 'Reinforce the strike fleet');
			$this->addGoal(209, max(1, (int) ceil($scale / 20)), 55, 'Collect debris');
			$this->addGoal(407, 1, 55, 'Shield dome');

			foreach ([109, 110, 111] as $id) {
				$this->addGoal($id, max(3, intdiv($metalLevel, 2) - 1), 80, 'Combat technologies');
			}
		}

		if ($metalLevel >= 14) {
			$this->addGoal(
				206,
				max(3, (int) ceil($scale * $military / 8)),
				80,
				'Cruisers against light ships and rocket launchers',
			);
			$this->addGoal(402, (int) ceil($scale / 4), 40, 'Diversify defenses');
			$this->addGoal(115, max(6, intdiv($metalLevel, 2)), 55, 'Cargo ship speed');
		}

		if ($metalLevel >= 18) {
			$this->addGoal(207, max(3, (int) ceil($scale * $military / 15)), 85, 'Heavy strike fleet');
			$this->addGoal(404, max(2, (int) ceil($scale / 30)), 45, 'Defense against heavy fleets');
			$this->addGoal(15, max(1, intdiv($metalLevel - 16, 4)), 75, 'Speed up production');
		}

		if ($metalLevel >= 22) {
			$this->addGoal(215, max(3, (int) ceil($scale * $military / 25)), 80, 'Fleet for major raids');
		}

		$desired = min((int) config('game.maxPlanets', 9), max(1, intdiv($metalLevel - 5, 3)));

		if ($this->researchHub && $colonyCount < $desired) {
			$this->addGoal(150, $colonyCount, 110, 'Unlock the next colony');

			$limit = min((int) config('game.maxPlanets', 9), $this->planet->user->getTechLevel('colonization') + 1);

			if ($colonyCount < $limit && !$hasColonizer && ($this->units[208] ?? 0) === 0) {
				$this->addGoal(208, 1, 105, 'Prepare for colonization');
			}
		}
	}

	private function planMines(): void
	{
		$best = null;
		$bestValue = 0.0;
		$bestEnergy = 0.0;

		foreach ([
			1 => ['metal', 1.0],
			2 => ['crystal', 1.5],
			3 => ['deuterium', 2.0],
		] as $id => [$resource, $value]) {
			$entity = $this->getEntity($id);
			$delta = Building::getNextProduction($entity->getObject(), $entity->getLevel(), $this->planet);
			$price = $entity->getPrice();
			$cost = ResourceValue::sum($price);
			$efficiency = ($delta?->get($resource) ?? 0) * $value / max(1, $cost);

			// Без синтезатора начальные запасы дейтерия однажды закончатся.
			if ($id === 3 && $entity->getLevel() < 2 && $this->planet->getLevel(1) >= 4) {
				$efficiency *= 5;
			}

			if ($efficiency > $bestValue) {
				$bestValue = $efficiency;
				$best = $id;
				$bestEnergy = abs($delta?->get('energy') ?? 0);
			}
		}

		if (!$best) {
			return;
		}

		$needed = $this->planet->energy_used + $bestEnergy;

		if ($this->planet->energy < $needed) {
			$shortage = $this->planet->energy < $this->planet->energy_used;
			$this->addGoal(
				4,
				$this->planet->getLevel(4) + 1,
				$shortage ? 1000 : 100,
				$shortage ? 'Restore energy for active mines' : 'Energy for the next mine upgrade',
			);
		} else {
			$this->addGoal($best, $this->planet->getLevel($best) + 1, 100, 'Best production increase for the upgrade cost');
		}
	}

	private function loadUnits(): void
	{
		foreach ($this->planet->entities as $entity) {
			$this->units[$entity->entity_id] = $entity->amount;
		}

		foreach ($this->snapshot['queue'] as $item) {
			if (($item['planet_id'] === $this->planet->id || $item['object_id'] === 208)
				&& $item['type'] === QueueType::SHIPYARD->value) {
				$this->units[$item['object_id']] = ($this->units[$item['object_id']] ?? 0) + $item['level'];
			}
		}

		foreach ($this->snapshot['fleets'] as $flight) {
			$local = $flight['start_galaxy'] === $this->planet->galaxy
				&& $flight['start_system'] === $this->planet->system
				&& $flight['start_planet'] === $this->planet->planet
				&& $flight['start_type'] === $this->planet->planet_type->value;

			foreach (json_decode($flight['entities'], true, flags: JSON_THROW_ON_ERROR) as $entity) {
				if ($local || $entity['i'] === 208) {
					$this->units[$entity['i']] = ($this->units[$entity['i']] ?? 0) + $entity['c'];
				}
			}
		}
	}

	private function addGoal(
		int $id,
		int $target,
		float $score,
		string $reason,
		array $path = [],
	): void
	{
		if (in_array($id, $path, true)) {
			return;
		}

		$path[] = $id;
		$entity = $this->getEntity($id);
		$object = $entity->getObject();
		$type = $object->getType();
		$isUnit = in_array($type, [ItemType::FLEET, ItemType::DEFENSE], true);
		$current = $isUnit ? ($this->units[$id] ?? 0) : $entity->getLevel();
		$target = min($target, $object->getMaxConstructable() ?? $target);

		if ($current >= $target || ($type === ItemType::TECH && !$this->researchHub)) {
			return;
		}

		if ($object instanceof BuildingObject && !$object->hasAllowedBuild($this->planet->planet_type)) {
			return;
		}

		$requirements = $object->getRequeriments();

		if (isset($requirements['race']) && $requirements['race'] !== $this->planet->user->race) {
			return;
		}

		$available = true;

		foreach ($requirements as $requiredId => $level) {
			if ($requiredId === 'race') {
				continue;
			}

			if ($this->getEntity($requiredId)->getLevel() < $level) {
				$available = false;
				$this->addGoal($requiredId, $level, $score, $reason . ': meet prerequisites', $path);
			}
		}

		if (!$available) {
			return;
		}

		$price = $entity->getPrice();
		$capacity = $this->planet->getProduction()->getStorageCapacity();

		foreach ([
			22 => 'metal',
			23 => 'crystal',
			24 => 'deuterium',
		] as $storageId => $resource) {
			if (($price[$resource] ?? 0) > $capacity->get($resource)) {
				$available = false;
				$this->addGoal(
					$storageId,
					$this->planet->getLevel($storageId) + 1,
					$score + 10,
					'Storage capacity for goal: ' . $reason,
					$path,
				);
			}
		}

		if (!$available) {
			return;
		}

		if (($price['energy'] ?? 0) > $this->planet->energy) {
			$this->addGoal(4, $this->planet->getLevel(4) + 1, $score + 10, 'Energy for goal: ' . $reason, $path);

			return;
		}

		if (($this->recommendations[$id]['score'] ?? -1) >= $score) {
			return;
		}

		$this->recommendations[$id] = [
			'id' => $id,
			'count' => $isUnit ? min($target - $current, (int) config('ai.max_batch', 20)) : 1,
			'score' => $score,
			'reason' => $reason,
		];
	}
}
