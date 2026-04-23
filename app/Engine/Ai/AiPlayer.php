<?php

namespace App\Engine\Ai;

use App\Engine\Building;
use App\Engine\Entity\Defence;
use App\Engine\Entity\Research;
use App\Engine\Entity\Ship;
use App\Engine\EntityFactory;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\QueueType;
use App\Engine\Objects\BuildingObject;
use App\Engine\Objects\ObjectsFactory;
use App\Engine\Objects\ResearchObject;
use App\Engine\QueueManager;
use App\Facades\Galaxy;
use App\Models\Ai;
use App\Models\Planet;
use App\Services\UserService;

class AiPlayer
{
	protected QueueManager $queue;
	protected StrategyPlanner $planner;
	protected Planet $planet;

	public function __construct(protected Ai $ai)
	{
	}

	public function run(): void
	{
		UserService::checkLevelXp($this->ai->user);

		if (!$this->ai->user->planet_current && !$this->ai->user->planet_id && $this->ai->user->race) {
			Galaxy::createPlanetByUser($this->ai->user);
		}

		$planets = $this->ai->user->getPlanets();

		foreach ($planets as $planet) {
			$planet->getProduction()->update();
			$planet->checkUsedFields();
			$planet->setRelation('user', $this->ai->user);

			$planet->user->onlinetime = now();
			$planet->user->save();

			$this->planet = $planet;
			$this->queue = new QueueManager($planet);
			$this->planner = new StrategyPlanner($planet, $this->ai->strategy);

			$this->runEconomyActions();
		}
	}

	private function runEconomyActions(): bool
	{
		$canBuild = true;
		$canResearch = true;
		$canShipyard = true;
		$canDefense = true;

		if ($this->ai->strategy === StrategyType::ECONOMY) {
			$canResearch = random_int(1, 100) <= 30;
			$canShipyard = random_int(1, 100) <= 20;
			$canDefense = random_int(1, 100) <= 20;
		}

		if ($this->ai->strategy === StrategyType::MILITARY) {
			$canBuild = random_int(1, 100) <= 40;
			$canResearch = random_int(1, 100) <= 30;
		}

		if ($canBuild && $this->tryUpgradeBuildings()) {
			return true;
		}

		if ($canResearch && !$this->queue->getCount(QueueType::RESEARCH) && !Building::checkLabInQueue($this->planet)) {
			$priority = $this->planner->getRecommendations(ItemType::TECH);

			foreach ($priority as $item) {
				if ($this->tryQueueResearch($item['id'])) {
					return true;
				}
			}
		}

		if ($canShipyard) {
			$todo = $this->getShipyardTodo($this->ai->strategy);
			$priority = $this->planner->getRecommendations(ItemType::FLEET);

			foreach ($priority as $item) {
				$cnt = $todo[$item['id']] ?? 0;

				if ($cnt && $this->tryQueueShipyard($item['id'], $cnt)) {
					return true;
				}
			}
		}

		if ($canDefense) {
			$todo = $this->getDefenseTodo($this->ai->strategy);
			$priority = $this->planner->getRecommendations(ItemType::DEFENSE);

			foreach ($priority as $item) {
				$cnt = $todo[$item['id']] ?? 0;

				if ($cnt && $this->tryQueueShipyard($item['id'], $cnt)) {
					return true;
				}
			}
		}

		return false;
	}

	private function getShipyardTodo(StrategyType $startegy): array
	{
		return match ($startegy) {
			StrategyType::ECONOMY => [202 => 30, 203 => 10, 204 => 5, 209 => 15, 210 => 5],
			StrategyType::MILITARY => [202 => 20, 203 => 10, 204 => 30, 205 => 15, 206 => 10, 207 => 5, 209 => 5, 210 => 10],
			default => [202 => 25, 203 => 10, 204 => 15, 205 => 5, 206 => 3, 209 => 10, 210 => 5],
		};
	}

	private function getDefenseTodo(StrategyType $startegy): array
	{
		return match ($startegy) {
			StrategyType::ECONOMY => [401 => 20, 402 => 10, 407 => 1, 408 => 1],
			StrategyType::MILITARY => [401 => 10, 402 => 5, 407 => 1],
			default => [401 => 25, 402 => 15, 403 => 10, 404 => 3, 407 => 1, 408 => 1],
		};
	}

	private function tryUpgradeBuildings(): bool
	{
		$maxQueueSize = (int) config('game.maxBuildingQueue') + (int) $this->planet->user->bonus('queue', 0);

		if ($this->queue->getCount(QueueType::BUILDING) >= $maxQueueSize) {
			return false;
		}

		$energyFree = $this->planet->energy < $this->planet->energy_used;

		if ($energyFree < 0) {
			foreach ([4, 12] as $id) {
				if ($this->tryQueueBuilding($id)) {
					return true;
				}
			}
		}

		$candidates = $this->planner->getRecommendations(ItemType::BUILDING);

		foreach ($candidates as $item) {
			if ($this->tryQueueBuilding($item['id'])) {
				return true;
			}
		}

		return false;
	}

	/** @phpstan-ignore-next-line */
	private function pickMineToUpgrade(): int
	{
		$mLvl = $this->planet->getLevel(1);
		$cLvl = $this->planet->getLevel(2);
		$dLvl = $this->planet->getLevel(3);

		// keep Crystal within -2 of Metal
		if ($cLvl < $mLvl - 2) {
			return 2;
		}

		// keep Deut within -2 of Crystal
		if ($dLvl < $cLvl - 2) {
			return 3;
		}

		// otherwise metal
		return 1;
	}

	/** @phpstan-ignore-next-line */
	private function tryStorageIfNeeded(): bool
	{
		$storage = $this->planet->getProduction()->getStorageCapacity();
		$storageMap = [[22, 'metal'], [23, 'crystal'], [24, 'deuterium']];

		foreach ($storageMap as [$id, $resKey]) {
			$cap = $storage->get($resKey);
			$cur = $this->planet->{$resKey};

			if ($cap > 0 && $cur > $cap * 0.85 && $this->tryQueueBuilding($id)) {
				return true;
			}
		}

		return false;
	}

	private function tryQueueBuilding(int $id): bool
	{
		$object = ObjectsFactory::get($id);

		if (!($object instanceof BuildingObject)) {
			return false;
		}

		if (!$object->hasAllowedBuild($this->planet->planet_type)) {
			return false;
		}

		$entity = $this->planet->getEntityUnit($object);

		if (!$entity->canConstruct() || !$entity->isAvailable()) {
			return false;
		}

		$maxQueueSize = (int) config('game.maxBuildingQueue') + (int) $this->planet->user->bonus('queue', 0);

		if ($this->queue->getCount(QueueType::BUILDING) >= $maxQueueSize) {
			return false;
		}

		$this->queue->add($object);

		return true;
	}

	private function tryQueueResearch(int $id): bool
	{
		$object = ObjectsFactory::get($id);

		if (!($object instanceof ResearchObject)) {
			return false;
		}

		$entity = Research::createEntity(
			$object->getId(),
			$this->planet->user->getTechLevel($object->getId()),
			$this->planet
		);

		if (!$entity->isAvailable() || !$entity->canConstruct()) {
			return false;
		}

		if ($object->getMaxConstructable() && $this->planet->user->getTechLevel($object->getId()) >= $object->getMaxConstructable()) {
			return false;
		}

		$this->queue->add($object);

		return true;
	}

	private function tryQueueShipyard(int $id, int $count): bool
	{
		if (!$count) {
			return false;
		}

		$object = ObjectsFactory::get($id);
		$entity = EntityFactory::get($object->getId(), 1, $this->planet);

		if ((!($entity instanceof Ship) && !($entity instanceof Defence))) {
			return false;
		}

		if (!$entity->isAvailable()) {
			return false;
		}

		$buildItems = $this->queue->get(QueueType::SHIPYARD);

		if ($object->getMaxConstructable()) {
			$total = $this->planet->getLevel($object->getId());

			foreach ($buildItems as $item) {
				if ($item->object_id == $object->getId()) {
					$total += $item->level;
				}
			}

			$count = min($count, max(($object->getMaxConstructable() - $total), 0));
		}

		$count = min($count, $entity->getMaxConstructible());

		if (!$count) {
			return false;
		}

		$this->queue->add($object, $count);

		return true;
	}
}
