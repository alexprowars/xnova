<?php

namespace App\Engine;

use App\Engine\Enums\ItemType;
use App\Engine\Enums\MessageType;
use App\Engine\Enums\QueueConstructionType;
use App\Engine\Enums\QueueType;
use App\Engine\Messages\Types\QueueDestroyNotExistMessage;
use App\Engine\Messages\Types\QueueNoResourcesMessage;
use App\Engine\Objects\BaseObject;
use App\Engine\Objects\ObjectsFactory;
use App\Events\PlanetEntityUpdated;
use App\Exceptions\Exception;
use App\Facades\Vars;
use App\Models;
use App\Models\LogsHistory;
use App\Models\Planet;
use App\Notifications\SystemMessage;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class QueueManager
{
	/** @var Collection<array-key, Models\Queue>|null */
	protected ?Collection $queue = null;
	private Models\Queue|false|null $research = false;

	public function __construct(protected Planet $planet)
	{
	}

	public static function recalculateForUser(Models\User $user): void
	{
		$queue = $user->queue()
			->whereNotNull('date')
			->with('planet')
			->get();

		foreach ($queue as $item) {
			if (!$item->planet) {
				continue;
			}

			$item->planet->setRelation('user', $user);
			$item->update(['date_end' => $item->date->addSeconds($item->getTime())]);
		}
	}

	public function loadQueue(bool $forUpdate = false): void
	{
		$this->queue = $this->planet->user->queue()
			->orderBy('id')
			->whereBelongsTo($this->planet)
			->when($forUpdate, fn($query) => $query->lockForUpdate())
			->get()
			->map(fn(Models\Queue $item) => $item->setRelation('planet', $this->planet))
			->collect();

		$this->research = false;
	}

	public function getResearch(): ?Models\Queue
	{
		if ($this->research === false) {
			$this->research = $this->get(QueueType::RESEARCH)->first()
				?? $this->getUser()->queue()->where('type', QueueType::RESEARCH)->first();
		}

		return $this->research;
	}

	public function getPlanet(): Planet
	{
		return $this->planet;
	}

	public function getUser(): Models\User
	{
		return $this->planet->user;
	}

	public function add(BaseObject $element, int $count = 1, bool $destroy = false): ?Models\Queue
	{
		if ($element->getType() == ItemType::BUILDING) {
			return (new Queue\Build($this))->add($element, $destroy);
		} elseif ($element->getType() == ItemType::TECH) {
			return (new Queue\Tech($this))->add($element);
		} elseif ($element->getType() == ItemType::FLEET || $element->getType() == ItemType::DEFENSE) {
			return (new Queue\Unit($this))->add($element, $count);
		}

		return null;
	}

	public function delete(BaseObject $element, int $queueId = 0): void
	{
		if ($element->getType() == ItemType::BUILDING) {
			(new Queue\Build($this))->delete($queueId);
		} elseif ($element->getType() == ItemType::TECH) {
			(new Queue\Tech($this))->delete($element);
		}
	}

	/** @return Collection<array-key, Models\Queue> */
	public function get(?QueueType $type = null): Collection
	{
		if ($this->queue === null) {
			$this->loadQueue();
		}

		if (!$type) {
			return $this->queue;
		} elseif (in_array($type, QueueType::cases())) {
			return $this->queue->where('type', $type);
		}

		return new Collection();
	}

	public function getCount(?QueueType $type = null): int
	{
		return $this->get($type)->count();
	}

	public function deleteInQueue(Models\Queue $queueItem): bool
	{
		if (!$this->get()->firstWhere('id', $queueItem->id)) {
			return false;
		}

		if ($queueItem->delete()) {
			$this->queue = $this->queue->reject(fn(Models\Queue $item) => $item->is($queueItem));

			if ($queueItem->type === QueueType::RESEARCH) {
				$this->research = false;
			}

			return true;
		}

		return false;
	}

	public function update(): void
	{
		$user = $this->getUser();

		$this->planet->getConnection()->transaction(function () use ($user) {
			$user->refreshForUpdate();

			$this->planet->unsetRelation('user')
				->unsetRelation('entities')
				->refreshForUpdate();

			$this->planet->setRelation('user', $user);

			$this->updateLocked();
		});
	}

	private function updateLocked(): void
	{
		$this->planet->setRelation('entities', $this->planet->entities()->lockForUpdate()->get());
		$this->planet->getProduction()->reset();

		$this->loadQueue(true);

		$buildingsCount = $this->getCount(QueueType::BUILDING);

		if ($buildingsCount) {
			$this->nextBuildingQueue();

			for ($i = 0; $i < $buildingsCount; $i++) {
				$completedAt = $this->checkBuildQueue();

				if ($completedAt) {
					$this->planet->getProduction()->reset();
					$this->planet->getProduction()->update();

					$this->nextBuildingQueue($completedAt instanceof CarbonImmutable ? $completedAt : null);
				} else {
					break;
				}
			}
		}

		$this->checkTechQueue();
		$this->checkUnitQueue();
	}

	protected function checkBuildQueue(): CarbonImmutable|bool
	{
		$queueArray = $this->get(QueueType::BUILDING);

		if ($queueArray->isEmpty()) {
			return false;
		}

		$buildItem = $queueArray->first();

		if (!$buildItem->date) {
			return false;
		}

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

		if ($buildItem->date->timestamp + $buildTime <= now()->timestamp + 5) {
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

			return $buildItem->date_end;
		}

		return false;
	}

	public function nextBuildingQueue(?CarbonImmutable $startedAt = null): bool
	{
		$queueArray = $this->get(QueueType::BUILDING);

		if ($queueArray->isEmpty() || $queueArray->first()->date) {
			return false;
		}

		foreach ($queueArray as $buildItem) {
			if ($buildItem->object_id == 31 && config('game.BuildLabWhileRun', 0) != 1) {
				$researchInProgress = $this->planet->user->queue()
					->where('type', QueueType::RESEARCH)
					->exists();

				if ($researchInProgress) {
					return false;
				}
			}

			$haveNoMoreLevel = false;

			$entity = $this->planet->getEntityUnit($buildItem->object_id);

			if (!($entity instanceof Entity\Building)) {
				if (!$this->deleteInQueue($buildItem)) {
					$buildItem->delete();
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

				$buildStartedAt = $startedAt ?? now();

				$buildItem->update([
					'date' => $buildStartedAt,
					'date_end' => $buildStartedAt->addSeconds($buildTime),
					'level' => $entity->getLevel() + ($isDestroy ? 0 : 1),
				]);

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

				break;
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

				if (!$this->deleteInQueue($buildItem)) {
					$buildItem->delete();
				}
			}
		}

		$this->loadQueue(true);

		return true;
	}

	public function resumeBuildingQueues(?CarbonImmutable $startedAt = null): void
	{
		$user = $this->getUser();

		$user->getConnection()->transaction(function () use ($user, $startedAt) {
			$user->refreshForUpdate();

			if ($user->queue()->where('type', QueueType::RESEARCH)->exists()) {
				return;
			}

			$planetIds = $user->queue()
				->where('type', QueueType::BUILDING)
				->whereNull('date')
				->distinct()
				->orderBy('planet_id')
				->pluck('planet_id');

			foreach ($planetIds as $planetId) {
				$planet = $planetId == $this->planet->id
					? $this->planet->refreshForUpdate()
					: $user->planets()->lockForUpdate()->find($planetId);

				if (!$planet || $planet->trashed() || $planet->user_id != $user->id) {
					continue;
				}

				$planet->setRelation('user', $user);
				$planet->getProduction()->reset();

				$manager = $planet === $this->planet ? $this : new self($planet);
				$manager->loadQueue();
				$manager->nextBuildingQueue($startedAt);
			}
		});
	}

	public function checkTechQueue(): void
	{
		$queueItem = $this->getResearch();

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

		if ($queueItem->date->timestamp + $buildTime <= now()->timestamp + 5) {
			$this->planet->user->setTech($queueItem->object_id, $queueItem->level);

			if (!$this->deleteInQueue($queueItem)) {
				$queueItem->delete();
			}

			$this->research = false;

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
			$this->resumeBuildingQueues($queueItem->date_end);
		}
	}

	public function checkUnitQueue(): bool
	{
		$queue = $this->get(QueueType::SHIPYARD)->values();

		if ($queue->isEmpty()) {
			return false;
		}

		$missilesSpace = ($this->planet->getLevel('missile_facility') * 10) -
			($this->planet->getLevel('interceptor_misil') + (2 * $this->planet->getLevel('interplanetary_misil')));

		$max = [];
		$buildTypes = Vars::getObjectsByType([ItemType::FLEET, ItemType::DEFENSE]);

		foreach ($buildTypes as $object) {
			if ($object->getMaxConstructable()) {
				$max[$object->getId()] = $this->planet->getLevel($object->getId());
			}
		}

		$builded = 0;

		foreach ($queue as $item) {
			$object = ObjectsFactory::get($item->object_id);

			if ($object->getMaxConstructable()) {
				$item->level = min($item->level, $object->getMaxConstructable());

				if ($max[$item->object_id] + $item->level > $object->getMaxConstructable()) {
					$item->level = $object->getMaxConstructable() - $max[$item->object_id];
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
			if (!isset($buildTypes[$item->object_id])) {
				continue;
			}

			$entity = $this->planet->getEntityUnit($item->object_id);

			$buildTime = $entity->getTime();

			while ($item->date->addSeconds($buildTime)->isPast()) {
				if ($item->object_id == 502 || $item->object_id == 503) {
					$requiredSpace = $item->object_id == 503 ? 2 : 1;

					if ($missilesSpace < $requiredSpace) {
						break;
					}

					$missilesSpace -= $requiredSpace;
				}

				$item->date = $item->date->addSeconds($buildTime);

				$builded++;
				$this->planet->updateAmount($item->object_id, 1, true);
				$item->level--;

				$isUpdated = true;

				if ($item->level <= 0) {
					if (!$this->deleteInQueue($item)) {
						$item->delete();
					}

					$nextItem = $queue->get($i + 1);

					if ($nextItem && $nextItem->date->lessThan($item->date)) {
						$nextItem->date = $item->date;
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
