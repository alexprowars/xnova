<?php

namespace App\Engine;

use App\Engine\Contracts\EntityBuildingInterface;
use App\Events\PlanetEntityUpdated;
use App\Exceptions\ErrorException;
use App\Format;
use App\Helpers;
use App\Models;
use App\Models\LogHistory;
use App\Models\Planet;
use App\Models\User;

class QueueManager
{
	/** @var Models\Queue[]|bool */
	private $queue = false;

	public const TYPE_BUILDING = Models\Queue::TYPE_BUILD;
	public const TYPE_RESEARCH = Models\Queue::TYPE_TECH;
	public const TYPE_SHIPYARD = Models\Queue::TYPE_UNIT;

	protected User $user;

	public function __construct($user, protected ?Planet $planet = null)
	{
		if ($user instanceof Models\User) {
			$this->user = $user;
		} else {
			$this->user = Models\User::find((int) $user);
		}

		$this->loadQueue();
	}

	public function loadQueue()
	{
		$query = $this->user->queue()
			->orderBy('id', 'ASC');

		if ($this->planet) {
			$query->where('planet_id', $this->planet->id);
		}

		$this->queue = $query->get()->all();
	}

	public function setPlanet(Planet $planet)
	{
		$this->planet = $planet;
	}

	public function getPlanet()
	{
		return $this->planet;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function add($elementId, $count = 1, $destroy = false)
	{
		$elementId = (int) $elementId;

		$type = Vars::getItemType($elementId);

		if ($type == Vars::ITEM_TYPE_BUILING) {
			(new Queue\Build($this)
			)->add($elementId, $destroy);
		} elseif ($type == Vars::ITEM_TYPE_TECH) {
			(new Queue\Tech($this)
			)->add($elementId);
		} elseif ($type == Vars::ITEM_TYPE_FLEET || $type == Vars::ITEM_TYPE_DEFENSE) {
			(new Queue\Unit($this)
			)->add($elementId, $count);
		}
	}

	public function delete($elementId, $listId = 0)
	{
		$elementId = (int) $elementId;

		$type = Vars::getItemType($elementId);

		if ($type == Vars::ITEM_TYPE_BUILING) {
			(new Queue\Build($this)
			)->delete($listId);
		} elseif ($type == Vars::ITEM_TYPE_TECH) {
			(new Queue\Tech($this)
			)->delete($elementId);
		}
	}

	public function get($type = '')
	{
		if (!is_array($this->queue)) {
			$this->loadQueue();
		}

		if (!$type) {
			return $this->queue;
		} elseif (in_array($type, [self::TYPE_BUILDING, self::TYPE_RESEARCH, self::TYPE_SHIPYARD])) {
			$r = [];

			foreach ($this->queue as $item) {
				if ($item->type == $type) {
					$r[] = $item;
				}
			}

			return $r;
		} else {
			return [];
		}
	}

	public function getCount($queueType = '')
	{
		if (!is_array($this->queue)) {
			$this->loadQueue();
		}

		if (!$queueType) {
			return count($this->queue);
		} elseif (in_array($queueType, [self::TYPE_BUILDING, self::TYPE_RESEARCH, self::TYPE_SHIPYARD])) {
			$cnt = 0;

			foreach ($this->queue as $item) {
				if ($item->type == $queueType) {
					$cnt++;
				}
			}

			return $cnt;
		} else {
			return 0;
		}
	}

	public function getTypes()
	{
		return [self::TYPE_BUILDING, self::TYPE_RESEARCH, self::TYPE_SHIPYARD];
	}

	public function deleteInQueue($id)
	{
		foreach ($this->queue as $i => $item) {
			if ($item->id == $id) {
				if ($item->delete()) {
					unset($this->queue[$i]);

					return true;
				}

				break;
			}
		}

		return false;
	}

	public function update()
	{
		if (!($this->planet instanceof Planet)) {
			throw new ErrorException('Произошла внутренняя ошибка: Queue::update::check::Planet');
		}

		$buildingsCount = $this->getCount(self::TYPE_BUILDING);

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
		//$this->checkUnitQueue();

		return true;
	}

	private function checkBuildQueue()
	{
		$queueArray = $this->get(self::TYPE_BUILDING);

		if (empty($queueArray)) {
			return false;
		}

		$buildItem = $queueArray[0];

		$entity = $this->planet->getEntity($buildItem->object_id);

		if (!($entity instanceof EntityBuildingInterface)) {
			if (!$this->deleteInQueue($buildItem->id)) {
				$buildItem->delete();
			}

			return true;
		}

		$isDestroy = $buildItem->operation == Models\Queue::OPERATION_DESTROY;
		$buildTime = $entity->getTime();

		if ($isDestroy) {
			$buildTime = ceil($buildTime / 2);
		}

		$buildItem->time_end = $buildItem->time->addSeconds($buildTime);
		$buildItem->save();

		if ($buildItem->time->timestamp + $buildTime <= time() + 5) {
			if (!$this->planet->planet_updated) {
				$this->planet->getProduction()->update(true);
			}

			$this->addExp($entity, $isDestroy);

			if (!$isDestroy) {
				$entity->amount++;
			} else {
				$entity->amount--;
			}

			event(new PlanetEntityUpdated($this->user->id));

			if (!$this->deleteInQueue($buildItem->id)) {
				$buildItem->delete();
			}

			if (config('settings.log.buildings', false)) {
				LogHistory::create([
					'user_id' 			=> $this->user->id,
					'operation' 		=> 9,
					'planet' 			=> $this->planet->id,
					'from_metal' 		=> $this->planet->metal,
					'from_crystal' 		=> $this->planet->crystal,
					'from_deuterium' 	=> $this->planet->deuterium,
					'to_metal' 			=> $this->planet->metal,
					'to_crystal' 		=> $this->planet->crystal,
					'to_deuterium' 		=> $this->planet->deuterium,
					'entity_id' 		=> $entity->entity_id,
					'amount' 			=> $entity->amount,
				]);
			}

			return true;
		}

		return false;
	}

	public function nextBuildingQueue()
	{
		$queueArray = $this->get(self::TYPE_BUILDING);

		if (!count($queueArray) || $queueArray[0]->time) {
			return false;
		}

		$loop = true;

		while ($loop) {
			$buildItem = $queueArray[0];

			$haveNoMoreLevel = false;

			$entity = $this->planet->getEntity($buildItem->object_id);

			if (!($entity instanceof EntityBuildingInterface)) {
				array_shift($queueArray);

				if (!$this->deleteInQueue($buildItem->id)) {
					$buildItem->delete();
				}

				if (!count($queueArray)) {
					$loop = false;
				}

				continue;
			}

			$isDestroy = $buildItem->operation == $buildItem::OPERATION_DESTROY;

			$cost = $isDestroy
				? $entity->getDestroyPrice()
				: $entity->getPrice();

			if ($isDestroy && $entity->amount == 0) {
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
					'time' => now(),
					'time_end' => now()->addSeconds($buildTime),
				]);

				$loop = false;

				if (config('settings.log.buildings', false)) {
					LogHistory::create([
						'user_id' 			=> $this->user->id,
						'operation' 		=> ($isDestroy ? 2 : 1),
						'planet' 			=> $this->planet->id,
						'from_metal' 		=> $this->planet->metal + $cost['metal'],
						'from_crystal' 		=> $this->planet->crystal + $cost['crystal'],
						'from_deuterium' 	=> $this->planet->deuterium + $cost['deuterium'],
						'to_metal' 			=> $this->planet->metal,
						'to_crystal' 		=> $this->planet->crystal,
						'to_deuterium' 		=> $this->planet->deuterium,
						'entity_id' 		=> $buildItem->object_id,
						'amount' 			=> $entity->amount + 1,
					]);
				}
			} else {
				if ($haveNoMoreLevel) {
					$message = sprintf(__('main.sys_nomore_level'), __('main.tech.' . $buildItem->object_id));
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
					if (isset($cost['energy'], $this->planet->energy_max) && $cost['energy'] > $this->planet->energy_max) {
						$message .= Format::number($cost['energy'] - $this->planet->energy_max) . ' энергии<br>';
					}
				}

				if (isset($message)) {
					User::sendMessage($this->user->id, 0, 0, 99, __('main.sys_buildlist'), $message);
				}

				array_shift($queueArray);

				if (!$this->deleteInQueue($buildItem->id)) {
					$buildItem->delete();
				}

				if (!count($queueArray)) {
					$loop = false;
				}
			}
		}

		$this->loadQueue();

		return true;
	}

	public function checkTechQueue()
	{
		if (!($this->planet instanceof Planet)) {
			throw new ErrorException('Произошла внутренняя ошибка: Queue::checkTechQueue::check::Planet');
		}

		$buildItem = $this->user->queue()
			->where('type', Models\Queue::TYPE_TECH)->first();

		if (!$buildItem) {
			return;
		}

		if ($buildItem->planet_id != $this->planet->id) {
			$planet = Planet::find((int) $buildItem->planet_id);
			$planet?->setRelation('user', $this->user);
		} else {
			$planet = $this->planet;
		}

		if (!$planet) {
			throw new ErrorException('Произошла внутренняя ошибка: Queue::checkTechQueue::check::Planet object not found');
		}

		$entity = \App\Engine\Entity\Research::createEntity($buildItem->object_id, $buildItem->level, $planet);

		$buildTime = $entity->getTime();

		$buildItem->time_end = $buildItem->time->addSeconds($buildTime);
		$buildItem->save();

		if ($buildItem->time->timestamp + $buildTime <= time() + 5) {
			$this->user->setTech($buildItem->object_id, $buildItem->level);

			if (!$this->deleteInQueue($buildItem->id)) {
				$buildItem->delete();
			}

			if ($planet->id == $this->planet->id) {
				$this->loadQueue();
			}

			if (config('settings.log.research', false)) {
				LogHistory::create([
					'user_id' 			=> $this->user->id,
					'operation' 		=> 8,
					'planet' 			=> $planet->id,
					'from_metal' 		=> $planet->metal,
					'from_crystal' 		=> $planet->crystal,
					'from_deuterium' 	=> $planet->deuterium,
					'to_metal' 			=> $planet->metal,
					'to_crystal' 		=> $planet->crystal,
					'to_deuterium' 		=> $planet->deuterium,
					'entity_id' 		=> $buildItem->object_id,
					'amount' 			=> $buildItem->level,
				]);
			}

			$this->user->update();
		}
	}

	public function checkUnitQueue()
	{
		$queue = $this->get(self::TYPE_SHIPYARD);

		if (empty($queue)) {
			return false;
		}

		$missilesSpace = ($this->planet->getLevel('missile_facility') * 10) - ($this->planet->getLevel('interceptor_misil') + (2 * $this->planet->getLevel('interplanetary_misil')));

		$max = [];
		$buildTypes = Vars::getItemsByType([Vars::ITEM_TYPE_FLEET, Vars::ITEM_TYPE_DEFENSE]);

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
				} else {
					if ($item->level > floor($missilesSpace / 2)) {
						$item->level = floor($missilesSpace / 2);
					} else {
						$missilesSpace -= $item->level;
					}
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

		foreach ($queue as $i => $item) {
			if (!in_array($item->object_id, $buildTypes)) {
				continue;
			}

			$entity = $this->planet->getEntity($item->object_id);

			$buildTime = $entity->getTime();

			while ($item->time->addSeconds($buildTime)->isPast()) {
				$item->time = $item->time->addSeconds($buildTime);

				$builded++;
				$entity->amount--;
				$item->level--;

				if ($item->level <= 0) {
					if (!$this->deleteInQueue($item->id)) {
						$item->delete();
					}

					if (isset($queue[$i + 1])) {
						$queue[$i + 1]->time = $item->time;
					}

					break;
				}
			}

			$this->planet->update();

			if ($item->level > 0) {
				$item->time_end = $item->time->addSeconds($buildTime);
				$item->update();

				break;
			}
		}

		return $builded > 0;
	}

	public function addExp(Entity\Building $entity, $destroy = false)
	{
		$xp = 0;

		if (in_array($entity->entity_id, Vars::getItemsByType('build_exp'))) {
			$cost = $destroy ? $entity->getDestroyPrice() : $entity->getPrice();
			$units = $cost['metal'] + $cost['crystal'] + $cost['deuterium'];

			if (!$destroy) {
				$xp += floor($units / config('settings.buildings_exp_mult', 1000));
			} else {
				$xp -= floor($units / config('settings.buildings_exp_mult', 1000));
			}
		}

		if ($xp != 0 && $this->user->lvl_minier < config('settings.level.max_ind', 100)) {
			$this->user->xpminier += $xp;

			if ($this->user->xpminier < 0) {
				$this->user->xpminier = 0;
			}

			$this->user->update();
		}
	}
}
