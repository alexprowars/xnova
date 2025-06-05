<?php

namespace App\Engine;

use App\Engine\Enums\ItemType;
use App\Engine\Enums\MessageType;
use App\Engine\Enums\QueueConstructionType;
use App\Engine\Enums\QueueType;
use App\Events\PlanetEntityUpdated;
use App\Exceptions\Exception;
use App\Facades\Vars;
use App\Format;
use App\Helpers;
use App\Models;
use App\Models\LogHistory;
use App\Models\Planet;
use App\Notifications\MessageNotification;
use Illuminate\Support\Collection;

class QueueManager
{
	/** @var Models\Queue[]|null|Collection<array-key, Models\Queue> */
	protected $queue;

	public function __construct(protected Planet $planet)
	{
		$this->loadQueue();
	}

	public function loadQueue()
	{
		$this->queue = $this->planet->user->queue()
			->orderBy('id')
			->whereBelongsTo($this->planet)
			->get()
			->map(fn(Models\Queue $item) => $item->setRelation('planet', $item->planet))
			->collect();
	}

	public function getPlanet()
	{
		return $this->planet;
	}

	public function getUser()
	{
		return $this->planet->user;
	}

	public function add(int|string $elementId, int $count = 1, bool $destroy = false): void
	{
		$type = Vars::getItemType($elementId);

		if ($type == ItemType::BUILDING) {
			(new Queue\Build($this))->add($elementId, $destroy);
		} elseif ($type == ItemType::TECH) {
			(new Queue\Tech($this))->add($elementId);
		} elseif ($type == ItemType::FLEET || $type == ItemType::DEFENSE) {
			(new Queue\Unit($this))->add($elementId, $count);
		}
	}

	public function delete(int|string $elementId, $listId = 0)
	{
		$type = Vars::getItemType($elementId);

		if ($type == ItemType::BUILDING) {
			(new Queue\Build($this))->delete($listId);
		} elseif ($type == ItemType::TECH) {
			(new Queue\Tech($this))->delete($elementId);
		}
	}

	/** @return Collection<array-key, Models\Queue> */
	public function get(?QueueType $type = null): Collection
	{
		if (!$type) {
			return $this->queue;
		} elseif (in_array($type, QueueType::cases())) {
			return $this->queue->where('type', $type);
		} else {
			return new Collection();
		}
	}

	public function getCount(?QueueType $type = null)
	{
		if (!$type) {
			return $this->queue->count();
		} elseif (in_array($type, QueueType::cases())) {
			return $this->queue->where('type', $type)->count();
		} else {
			return 0;
		}
	}

	public function deleteInQueue(Models\Queue $queueItem)
	{
		if (!$this->queue->firstWhere('id', $queueItem->id)) {
			return false;
		}

		if ($queueItem->delete()) {
			$this->queue->reject(fn(Models\Queue $item) => $item->is($queueItem));

			return true;
		}

		return false;
	}

	public function update()
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

		return true;
	}

	protected function checkBuildQueue()
	{
		$queueArray = $this->get(QueueType::BUILDING);

		if ($queueArray->isEmpty()) {
			return false;
		}

		$buildItem = $queueArray->first();

		$entity = $this->planet->getEntity($buildItem->object_id)->unit();

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
				LogHistory::create([
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

	public function nextBuildingQueue()
	{
		$queueArray = $this->get(QueueType::BUILDING);

		if ($queueArray->isEmpty() || $queueArray->first()->date) {
			return false;
		}

		$loop = true;

		while ($loop) {
			$buildItem = $queueArray->first();

			$haveNoMoreLevel = false;

			$entity = $this->planet->getEntity($buildItem->object_id)->unit();

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
					LogHistory::create([
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
					$message = __('main.sys_nomore_level', ['item' => __('main.tech.' . $buildItem->object_id)]);
				} elseif (!$haveRessources) {
					$message = 'У вас недостаточно ресурсов чтобы начать строительство здания "' . __('main.tech.' . $buildItem->object_id) . '" на планете ' . $this->planet->name . ' ' . Helpers::buildPlanetAdressLink($this->planet->toArray()) . '.<br>Вам необходимо ещё: <br>';

					if ($cost['metal'] > $this->planet->metal) {
						$message .= Format::number($cost['metal'] - $this->planet->metal) . ' металла<br>';
					}
					if ($cost['crystal'] > $this->planet->crystal) {
						$message .= Format::number($cost['crystal'] - $this->planet->crystal) . ' кристалла<br>';
					}
					if ($cost['deuterium'] > $this->planet->deuterium) {
						$message .= Format::number($cost['deuterium'] - $this->planet->deuterium) . ' дейтерия<br>';
					}
					if (isset($cost['energy'], $this->planet->energy) && $cost['energy'] > $this->planet->energy) {
						$message .= Format::number($cost['energy'] - $this->planet->energy) . ' энергии<br>';
					}
				}

				if (isset($message)) {
					$this->planet->user->notify(new MessageNotification(null, MessageType::Queue, __('main.sys_buildlist'), $message));
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

	public function checkTechQueue()
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

		$entity = Entity\Research::createEntity($queueItem->object_id, $queueItem->level, $planet);

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
				LogHistory::create([
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

	public function checkUnitQueue()
	{
		$queue = $this->get(QueueType::SHIPYARD);

		if ($queue->isEmpty()) {
			return false;
		}

		$missilesSpace = ($this->planet->getLevel('missile_facility') * 10) -
			($this->planet->getLevel('interceptor_misil') + (2 * $this->planet->getLevel('interplanetary_misil')));

		$max = [];
		$buildTypes = Vars::getItemsByType([ItemType::FLEET, ItemType::DEFENSE]);

		foreach ($buildTypes as $id) {
			$price = Vars::getItemPrice($id);

			if (isset($price['max'])) {
				$max[$id] = $this->planet->getLevel($id);
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

			$price = Vars::getItemPrice($item->object_id);

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

			$entity = $this->planet->getEntity($item->object_id)->unit();

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

	public function addExp(Entity\Building $entity, $destroy = false)
	{
		$xp = 0;

		if (in_array($entity->entityId, Vars::getItemsByType(ItemType::BUILING_EXP))) {
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
