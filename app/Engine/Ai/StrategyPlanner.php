<?php

namespace App\Engine\Ai;

use App\Engine\Building;
use App\Engine\Entity\Entity;
use App\Engine\EntityFactory;
use App\Engine\Enums\FleetDirection;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\PlanetType;
use App\Engine\Enums\QueueType;
use App\Engine\Fleet\MissionType;
use App\Engine\Objects\BaseObject;
use App\Engine\Objects\BuildingObject;
use App\Engine\Objects\ObjectsFactory;
use App\Engine\QueueManager;
use App\Models\Fleet;
use App\Models\Planet;

class StrategyPlanner
{
	private array $recommendations = [];
	private array $units = [];
	private QueueManager $queue;

	public function __construct(private Planet $planet, private StrategyType $strategy, private bool $researchHub = true, private int $probeTarget = 7)
	{
		$this->queue = new QueueManager($planet);
	}

	/** @return array<int, array{id: int, count: int, score: float, reason: string}> */
	public function getRecommendations(ItemType $type): array
	{
		if (empty($this->recommendations)) {
			$this->plan();
		}

		$result = array_filter($this->recommendations, fn(array $item) => ObjectsFactory::get($item['id'])->getType() === $type);
		usort($result, fn(array $a, array $b) => $b['score'] <=> $a['score'] ?: $a['id'] <=> $b['id']);

		return $result;
	}

	/** @return Entity<BaseObject> */
	public function getEntity(int $id): Entity
	{
		$object = ObjectsFactory::get($id);
		$level = $object->getType() === ItemType::TECH ? $this->planet->user->getTechLevel($id) : $this->planet->getLevel($id);

		return EntityFactory::get($id, $level, $this->planet);
	}

	private function plan(): void
	{
		if ($this->planet->planet_type !== PlanetType::PLANET) {
			return;
		}

		$this->loadUnits();
		$this->planMines();

		$metalLevel = $this->planet->getLevel(1);
		$production = $this->planet->getProduction()->getResourceProduction();
		$storage = $this->planet->getProduction()->getStorageCapacity();

		foreach ([22 => 'metal', 23 => 'crystal', 24 => 'deuterium'] as $id => $resource) {
			$free = $storage->get($resource) - $this->planet->{$resource};

			if ($free < max(0, $production->get($resource)) * 2 || $this->planet->{$resource} >= $storage->get($resource) * 0.9) {
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

		if ($this->researchHub) {
			$colonies = $this->planet->user->planets()->whereNull('destroyed_at')->where('planet_type', PlanetType::PLANET)->with('entities')->get();

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
		$this->addGoal(210, min((int) config('ai.max_probes', 32), max(7, $metalLevel, $this->probeTarget)), 95, 'Espionage probes');
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
			$this->addGoal(206, max(3, (int) ceil($scale * $military / 8)), 80, 'Cruisers against light ships and rocket launchers');
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

		$colonies = $this->planet->user->planets()->where('planet_type', PlanetType::PLANET)->whereNull('destroyed_at')->count();
		$expanding = Fleet::query()->whereBelongsTo($this->planet->user)->where('mission', MissionType::Colonization)->where('mess', 0)->exists();
		$desired = min((int) config('game.maxPlanets', 9), max(1, intdiv($metalLevel - 5, 3)));

		if ($this->researchHub && !$expanding && $colonies < $desired) {
			$this->addGoal(150, $colonies, 110, 'Unlock the next colony');
			$this->addGoal(208, 1, 105, 'Prepare for colonization');
		}
	}

	private function planMines(): void
	{
		$best = null;
		$bestValue = 0.0;

		foreach ([1 => ['metal', 1.0], 2 => ['crystal', 1.5], 3 => ['deuterium', 2.0]] as $id => [$resource, $value]) {
			$entity = $this->getEntity($id);
			$delta = Building::getNextProduction($entity->getObject(), $entity->getLevel(), $this->planet);
			$price = $entity->getPrice();
			$cost = $price['metal'] + $price['crystal'] * 1.5 + $price['deuterium'] * 2;
			$efficiency = ($delta?->get($resource) ?? 0) * $value / max(1, $cost);

			// Без синтезатора начальные запасы дейтерия однажды закончатся.
			if ($id === 3 && $entity->getLevel() < 2 && $this->planet->getLevel(1) >= 4) {
				$efficiency *= 5;
			}

			if ($efficiency > $bestValue) {
				$bestValue = $efficiency;
				$best = $id;
			}
		}

		if (!$best) {
			return;
		}

		$delta = Building::getNextProduction(ObjectsFactory::get($best), $this->planet->getLevel($best), $this->planet);
		$needed = $this->planet->energy_used + abs($delta?->get('energy') ?? 0);

		if ($this->planet->energy < $needed) {
			$this->addGoal(4, $this->planet->getLevel(4) + 1, 1000, 'Energy for active mines and the next upgrade');
		} else {
			$this->addGoal($best, $this->planet->getLevel($best) + 1, 100, 'Best production increase for the upgrade cost');
		}
	}

	private function loadUnits(): void
	{
		foreach ($this->planet->entities as $entity) {
			$this->units[$entity->entity_id] = $entity->amount;
		}

		foreach ($this->queue->get(QueueType::SHIPYARD) as $item) {
			$this->units[$item->object_id] = ($this->units[$item->object_id] ?? 0) + $item->level;
		}

		$flights = Fleet::query()->whereBelongsTo($this->planet->user)
			->coordinates(FleetDirection::START, $this->planet->coordinates)->get();

		foreach ($flights as $flight) {
			foreach ($flight->entities as $entity) {
				$this->units[$entity->id] = ($this->units[$entity->id] ?? 0) + $entity->count;
			}
		}
	}

	private function addGoal(int $id, int $target, float $score, string $reason, array $path = []): void
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

		foreach ([22 => 'metal', 23 => 'crystal', 24 => 'deuterium'] as $storageId => $resource) {
			if (($price[$resource] ?? 0) > $capacity->get($resource)) {
				$available = false;
				$this->addGoal($storageId, $this->planet->getLevel($storageId) + 1, $score + 10, 'Storage capacity for goal: ' . $reason, $path);
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
