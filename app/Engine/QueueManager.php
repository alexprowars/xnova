<?php

namespace App\Engine;

use App\Engine\Enums\ItemType;
use App\Engine\Enums\MessageType;
use App\Engine\Enums\QueueConstructionType;
use App\Engine\Enums\QueueType;
use App\Engine\Messages\Types\QueueDestroyNotExistMessage;
use App\Engine\Messages\Types\QueueNoResourcesMessage;
use App\Engine\Objects\BaseObject;
use App\Engine\Objects\BuildingObject;
use App\Engine\Objects\ObjectsFactory;
use App\Events\PlanetEntityUpdated;
use App\Exceptions\Exception;
use App\Facades\Vars;
use App\Models;
use App\Models\LogsHistory;
use App\Models\Planet;
use App\Notifications\SystemMessage;
use Illuminate\Support\Collection;

class QueueManager
{
	/** @var Models\Queue[]|null|Collection<array-key, Models\Queue> */
	protected mixed $queue;

	public function __construct(protected Planet $planet)
	{
		$this->loadQueue();
	}

	public function loadQueue(): void
	{
		$this->queue = $this->planet->user->queue()
			->orderBy('id')
			->whereBelongsTo($this->planet)
			->get()
			->map(fn(Models\Queue $item) => $item->setRelation('planet', $item->planet))
			->collect();
	}

	public function getPlanet(): Planet
	{
		return $this->planet;
	}

	public function getUser(): Models\User
	{
		return $this->planet->user;
	}

	public function add(BaseObject $element, int $count = 1, bool $destroy = false): void
	{
		$type = Vars::getItemType($element->getId());

		if ($type == ItemType::BUILDING) {
			(new Queue\Build($this))->add($element, $destroy);
		} elseif ($type == ItemType::TECH) {
			(new Queue\Tech($this))->add($element);
		} elseif ($type == ItemType::FLEET || $type == ItemType::DEFENSE) {
			(new Queue\Unit($this))->add($element, $count);
		}
	}

	public function delete(BaseObject $element, int $listId = 0): void
	{
		$type = Vars::getItemType($element->getId());

		if ($type == ItemType::BUILDING) {
			(new Queue\Build($this))->delete($listId);
		} elseif ($type == ItemType::TECH) {
			(new Queue\Tech($this))->delete($element);
		}
	}

	/** @return Collection<array-key, Models\Queue> */
	public function get(?QueueType $type = null): Collection
	{
		if (!$type) {
			return $this->queue;
		} elseif (in_array($type, QueueType::cases())) {
			return $this->queue->where('type', $type);
		}

		return new Collection();
	}

	public function getCount(?QueueType $type = null): int
	{
		if (!$type) {
			return $this->queue->count();
		} elseif (in_array($type, QueueType::cases())) {
			return $this->queue->where('type', $type)->count();
		}

		return 0;
	}

	public function deleteInQueue(Models\Queue $queueItem): bool
	{
		if (!$this->queue->firstWhere('id', $queueItem->id)) {
			return false;
		}

		if ($queueItem->delete()) {
			$this->queue = $this->queue->reject(fn(Models\Queue $item) => $item->is($queueItem));

			return true;
		}

		return false;
	}

	public function update(): void
	{
		$buildingsCount = $this->getCount(QueueType::BUILDING);

		if ($buildingsCount) {
			$this->nextBuildingQueue();

			for ($i = 0; $i < $buildingsCount; $i++) {
				if ($this->checkBuildQueue()) {
					$this->planet->update();
					$this->planet->getProduction()->update();

					$this->nextBuildingQueue();
				} else {
					break;
				}
			}
		}

		$this->checkTechQueue();
		$this->checkUnitQueue();
	}

	protected function checkBuildQueue(): bool
	{
		$queueArray = $this->get(QueueType::BUILDING);

		if ($queueArray->isEmpty()) {
			return false;
		}

		$buildItem = $queueArray->first();

		$entity = $this->planet->getEntityUnit($buildItem->object_id);

		if (!($entity instanceof Entity\Building)) {
			if (!$this->deleteInQueue($buildItem)) {
				$buildItem->delete();
			}

			return true;
		}

		$isDestroy = $buildItem->operation == QueueConstructionType::DESTROY;
		$buildTime = $entity->getTime();

		if ($isDestroy) {
			$buildTime = ceil($buildTime / 2);
		}

		$buildItem->date_end = $buildItem->date->addSeconds($buildTime);
		$buildItem->save();

		if ($buildItem->date->timestamp + $buildTime <= time() + 5) {
			if (!$this->planet->planet_updated) {
				$this->planet->getProduction()->update(true);
			}

			$this->addExp($entity, $isDestroy);

			if (!$isDestroy) {
				$this->planet->updateAmount($buildItem->object_id, 1, true);
			} else {
				$this->planet->updateAmount($buildItem->object_id, -1, true);
			}

			event(new PlanetEntityUpdated($this->planet));

			if (!$this->deleteInQueue($buildItem)) {
				$buildItem->delete();
			}

			if (config('game.log.buildings', false)) {
				LogsHistory::create([
					'user_id' 			=> $this->planet->user->id,
					'operation' 		=> 9,
					'planet' 			=> $this->planet->id,
					'from_metal' 		=> $this->planet->metal,
					'from_crystal' 		=> $this->planet->crystal,
					'from_deuterium' 	=> $this->planet->deuterium,
					'to_metal' 			=> $this->planet->metal,
					'to_crystal' 		=> $this->planet->crystal,
					'to_deuterium' 		=> $this->planet->deuterium,
					'entity_id' 		=> $entity->entityId,
					'amount' 			=> $entity->getLevel(),
				]);
			}

			return true;
		}

		return false;
	}

	public function nextBuildingQueue(): bool
	{
		$queueArray = $this->get(QueueType::BUILDING);

		if ($queueArray->isEmpty() || $queueArray->first()->date) {
			return false;
		}

		$loop = true;

		while ($loop) {
			$buildItem = $queueArray->first();

			$haveNoMoreLevel = false;

			$entity = $this->planet->getEntityUnit($buildItem->object_id);

			if (!($entity instanceof Entity\Building)) {
				$queueArray->shift();

				if (!$this->deleteInQueue($buildItem)) {
					$buildItem->delete();
				}

				if ($queueArray->isEmpty()) {
					$loop = false;
				}

				continue;
			}

			$isDestroy = $buildItem->operation == QueueConstructionType::DESTROY;

			$cost = $isDestroy
				? $entity->getDestroyPrice() : $entity->getPrice();

			if ($isDestroy && $entity->getLevel() == 0) {
				$haveRessources = false;
				$haveNoMoreLevel = true;
			} else {
				$haveRessources = $entity->canConstruct($isDestroy ? $entity->getDestroyPrice() : null);
			}

			if ($haveRessources && ($entity->isAvailable() || $isDestroy)) {
				$this->planet->metal 		-= $cost['metal'];
				$this->planet->crystal 		-= $cost['crystal'];
				$this->planet->deuterium 	-= $cost['deuterium'];
				$this->planet->update();

				$buildTime = $entity->getTime();

				if ($isDestroy) {
					$buildTime = ceil($buildTime / 2);
				}

				$buildItem->update([
					'date' => now(),
					'date_end' => now()->addSeconds($buildTime),
				]);

				$loop = false;

				if (config('game.log.buildings', false)) {
					LogsHistory::create([
						'user_id' 			=> $this->planet->user->id,
						'operation' 		=> ($isDestroy ? 2 : 1),
						'planet' 			=> $this->planet->id,
						'from_metal' 		=> $this->planet->metal + $cost['metal'],
						'from_crystal' 		=> $this->planet->crystal + $cost['crystal'],
						'from_deuterium' 	=> $this->planet->deuterium + $cost['deuterium'],
						'to_metal' 			=> $this->planet->metal,
						'to_crystal' 		=> $this->planet->crystal,
						'to_deuterium' 		=> $this->planet->deuterium,
						'entity_id' 		=> $buildItem->object_id,
						'amount' 			=> $entity->getLevel() + 1,
					]);
				}
			} else {
				if ($haveNoMoreLevel) {
					$this->planet->user->notify(
						new SystemMessage(MessageType::Queue, new QueueDestroyNotExistMessage(['object' => $buildItem->object_id]))
					);
				} elseif (!$haveRessources) {
					$message = [
						'object' => $buildItem->object_id,
						'planet' => [
							'name' => $this->planet->name,
							...$this->planet->coordinates->toArray(),
						],
					];

					if ($cost['metal'] > $this->planet->metal) {
						$message['metal'] = (int) ceil($cost['metal'] - $this->planet->metal);
					}

					if ($cost['crystal'] > $this->planet->crystal) {
						$message['crystal'] = (int) ceil($cost['crystal'] - $this->planet->crystal);
					}

					if ($cost['deuterium'] > $this->planet->deuterium) {
						$message['deuterium'] = (int) ceil($cost['deuterium'] - $this->planet->deuterium);
					}

					if (isset($cost['energy']) && $cost['energy'] > $this->planet->energy) {
						$message['energy'] = (int) ceil($cost['energy'] - $this->planet->energy);
					}

					$this->planet->user->notify(
						new SystemMessage(MessageType::Queue, new QueueNoResourcesMessage($message))
					);
				}

				$queueArray->shift();

				if (!$this->deleteInQueue($buildItem)) {
					$buildItem->delete();
				}

				if ($queueArray->isEmpty()) {
					$loop = false;
				}
			}
		}

		$this->loadQueue();

		return true;
	}

	public function checkTechQueue(): void
	{
		$queueItem = $this->planet->user->queue()
			->where('type', QueueType::RESEARCH)->first();

		if (!$queueItem) {
			return;
		}

		if ($queueItem->planet_id != $this->planet->id) {
			$planet = $queueItem->planet;
			$planet?->setRelation('user', $this->planet->user);
		} else {
			$planet = $this->planet;
		}

		if (!$planet) {
			throw new Exception('Queue::checkTechQueue::check::Planet object not found');
		}

		$entity = Entity\Research::createEntity($queueItem->object_id, $queueItem->level - 1, $planet);

		$buildTime = $entity->getTime();

		$queueItem->date_end = $queueItem->date->addSeconds($buildTime);
		$queueItem->save();

		if ($queueItem->date->timestamp + $buildTime <= time() + 5) {
			$this->planet->user->setTech($queueItem->object_id, $queueItem->level);

			if (!$this->deleteInQueue($queueItem)) {
				$queueItem->delete();
			}

			event(new PlanetEntityUpdated($this->planet));

			if ($planet->id == $this->planet->id) {
				$this->loadQueue();
			}

			if (config('game.log.research', false)) {
				LogsHistory::create([
					'user_id' 			=> $this->planet->user->id,
					'operation' 		=> 8,
					'planet' 			=> $planet->id,
					'from_metal' 		=> $planet->metal,
					'from_crystal' 		=> $planet->crystal,
					'from_deuterium' 	=> $planet->deuterium,
					'to_metal' 			=> $planet->metal,
					'to_crystal' 		=> $planet->crystal,
					'to_deuterium' 		=> $planet->deuterium,
					'entity_id' 		=> $queueItem->object_id,
					'amount' 			=> $queueItem->level,
				]);
			}

			$this->planet->user->update();
		}
	}

	public function checkUnitQueue(): bool
	{
		$queue = $this->get(QueueType::SHIPYARD);

		if ($queue->isEmpty()) {
			return false;
		}

		$missilesSpace = ($this->planet->getLevel('missile_facility') * 10) -
			($this->planet->getLevel('interceptor_misil') + (2 * $this->planet->getLevel('interplanetary_misil')));

		$max = [];
		$buildTypes = Vars::getObjectsByType([ItemType::FLEET, ItemType::DEFENSE]);

		foreach ($buildTypes as $object) {
			$price = $object->getPrice();

			if (isset($price['max'])) {
				$max[$object->getId()] = $this->planet->getLevel($object->getId());
			}
		}

		$builded = 0;

		foreach ($queue as $item) {
			if ($item->object_id == 502 || $item->object_id == 503) {
				if ($item->object_id == 502) {
					if ($item->level > $missilesSpace) {
						$item->level = $missilesSpace;
					} else {
						$missilesSpace -= $item->level;
					}
				} elseif ($item->level > floor($missilesSpace / 2)) {
					$item->level = (int) floor($missilesSpace / 2);
				} else {
					$missilesSpace -= $item->level;
				}
			}

			$price = ObjectsFactory::get($item->object_id)->getPrice();

			if (isset($price['max'])) {
				if ($item->level > $price['max']) {
					$item->level = $price['max'];
				}

				if ($max[$item->object_id] + $item->level > $price['max']) {
					$item->level = $price['max'] - $max[$item->object_id];
				}

				if ($item->level > 0) {
					$max[$item->object_id] += $item->level;
				} else {
					$item->level = 0;
				}
			}
		}

		$isUpdated = false;

		foreach ($queue as $i => $item) {
			if (!in_array($item->object_id, $buildTypes)) {
				continue;
			}

			$entity = $this->planet->getEntityUnit($item->object_id);

			$buildTime = $entity->getTime();

			while ($item->date->addSeconds($buildTime)->isPast()) {
				$item->date = $item->date->addSeconds($buildTime);

				$builded++;
				$this->planet->updateAmount($item->object_id, 1, true);
				$item->level--;

				$isUpdated = true;

				if ($item->level <= 0) {
					if (!$this->deleteInQueue($item)) {
						$item->delete();
					}

					if ($queue->get($i + 1)) {
						$queue->get($i + 1)->date = $item->date;
					}

					break;
				}
			}

			$this->planet->update();

			if ($item->level > 0) {
				$item->date_end = $item->date->addSeconds($buildTime);
				$item->update();

				break;
			}
		}

		if ($isUpdated) {
			event(new PlanetEntityUpdated($this->planet));
		}

		return $builded > 0;
	}

	public function addExp(Entity\Building $entity, bool $destroy = false): void
	{
		$xp = 0;

		/** @var BuildingObject $object */
		$object = $entity->getObject();

		if ($object->hasExperience()) {
			if (!$destroy) {
				$xp += $entity->getExp();
			} else {
				$xp -= $entity->getExp($destroy);
			}
		}

		if ($xp != 0 && $this->planet->user->lvl_minier < config('game.level.max_ind', 100)) {
			$this->planet->user->xpminier += $xp;

			if ($this->planet->user->xpminier < 0) {
				$this->planet->user->xpminier = 0;
			}

			$this->planet->user->update();
		}
	}
}
