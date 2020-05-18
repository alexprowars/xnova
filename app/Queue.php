<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace Xnova;

use Xnova\Exceptions\ErrorException;
use Xnova\Models\LogHistory;
use Xnova\Queue\Build;
use Xnova\Queue\Tech;
use Xnova\Queue\Unit;
use Xnova\Entity;

class Queue
{
	/** @var Models\Queue[]|bool */
	private $queue = false;

	public const TYPE_BUILDING = Models\Queue::TYPE_BUILD;
	public const TYPE_RESEARCH = Models\Queue::TYPE_TECH;
	public const TYPE_SHIPYARD = Models\Queue::TYPE_UNIT;
	/** @var User user */
	private $user;
	/** @var Planet planet */
	private $planet;

	public function __construct($user, ?Planet $planet = null)
	{
		if ($user instanceof Models\User) {
			$this->user = $user;
		} else {
			$this->user = Models\User::find((int) $user);
		}

		$this->planet = $planet;

		$this->loadQueue();
	}

	public function loadQueue()
	{
		$this->queue = [];

		$query = Models\Queue::query()->where('user_id', $this->user->getId())
			->orderBy('id', 'ASC');

		if ($this->planet) {
			$query->where('planet_id', $this->planet->id);
		}

		$items = $query->get();

		foreach ($items as $item) {
			$this->queue[] = $item;
		}
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
			(new Build($this)
			)->add($elementId, $destroy);
		} elseif ($type == Vars::ITEM_TYPE_TECH) {
			(new Tech($this)
			)->add($elementId);
		} elseif ($type == Vars::ITEM_TYPE_FLEET || $type == Vars::ITEM_TYPE_DEFENSE) {
			(new Unit($this)
			)->add($elementId, $count);
		}
	}

	public function delete($elementId, $listId = 0)
	{
		$elementId = (int) $elementId;

		$type = Vars::getItemType($elementId);

		if ($type == Vars::ITEM_TYPE_BUILING) {
			(new Build($this)
			)->delete($listId);
		} elseif ($type == Vars::ITEM_TYPE_TECH) {
			(new Tech($this)
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

		if (!($this->user instanceof User)) {
			throw new ErrorException('Произошла внутренняя ошибка: Queue::update::check::User');
		}

		$buildingsCount = $this->getCount(self::TYPE_BUILDING);

		if ($buildingsCount) {
			$this->nextBuildingQueue();

			for ($i = 0; $i < $buildingsCount; $i++) {
				if ($this->checkBuildQueue()) {
					$this->planet->update();
					$this->planet->resourceProductions();

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

		if (!count($queueArray)) {
			return false;
		}

		$buildItem = $queueArray[0];

		$build = $this->planet->getBuild($buildItem->object_id);
		$isDestroy = $buildItem->operation == Models\Queue::OPERATION_DESTROY;

		$entity = new Entity\Building($buildItem->object_id, $build['level'], new Entity\Context($this->user, $this->planet));

		$buildTime = $entity->getTime();

		if ($isDestroy) {
			$buildTime = ceil($buildTime / 2);
		}

		if ($buildItem->time + $buildTime != $buildItem->time_end) {
			$buildItem->update([
				'time_end' => $buildItem->time + $buildTime
			]);
		}

		if ($buildItem->time + $buildTime <= time() + 5) {
			if (!$this->planet->planet_updated) {
				$this->planet->resourceUpdate(0, true);
			}

			$cost = $isDestroy ? $entity->getDestroyPrice() : $entity->getPrice();
			$units = $cost['metal'] + $cost['crystal'] + $cost['deuterium'];

			$xp = 0;

			if (in_array($buildItem->object_id, Vars::getItemsByType('build_exp'))) {
				if (!$isDestroy) {
					$xp += floor($units / config('game.buildings_exp_mult', 1000));
				} else {
					$xp -= floor($units / config('game.buildings_exp_mult', 1000));
				}
			}

			if (!$isDestroy) {
				$this->planet->field_current++;
				$this->planet->setBuild($buildItem->object_id, $build['level'] + 1);
			} else {
				$this->planet->field_current--;
				$this->planet->setBuild($buildItem->object_id, $build['level'] - 1);
			}

			if (!$this->deleteInQueue($buildItem->id)) {
				$buildItem->delete();
			}

			if ($xp != 0 && $this->user->lvl_minier < config('game.level.max_ind', 100)) {
				$this->user->xpminier += $xp;

				if ($this->user->xpminier < 0) {
					$this->user->xpminier = 0;
				}

				$this->user->update();
			}

			if (config('game.log.buildings', false) == true) {
				LogHistory::query()->insert([
					'user_id' 			=> $this->user->id,
					'time' 				=> time(),
					'operation' 		=> 9,
					'planet' 			=> $this->planet->id,
					'from_metal' 		=> $this->planet->metal,
					'from_crystal' 		=> $this->planet->crystal,
					'from_deuterium' 	=> $this->planet->deuterium,
					'to_metal' 			=> $this->planet->metal,
					'to_crystal' 		=> $this->planet->crystal,
					'to_deuterium' 		=> $this->planet->deuterium,
					'build_id' 			=> $buildItem->object_id,
					'level' 			=> $this->planet->getBuildLevel($buildItem->object_id)
				]);
			}

			return true;
		}

		return false;
	}

	public function nextBuildingQueue()
	{
		$queueArray = $this->get(self::TYPE_BUILDING);

		if (!count($queueArray) || $queueArray[0]->time > 0) {
			return false;
		}

		$loop = true;

		while ($loop) {
			$buildItem = $queueArray[0];

			$HaveNoMoreLevel = false;

			$build = $this->planet->getBuild($buildItem->object_id);

			if (!$build) {
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

			$entity = new Entity\Building($buildItem->object_id, 0, new Entity\Context($this->user, $this->planet));
			$cost = $isDestroy ? $entity->getDestroyPrice() : $entity->getPrice();

			if ($isDestroy && $build['level'] == 0) {
				$HaveRessources = false;
				$HaveNoMoreLevel = true;
			} else {
				$HaveRessources = $entity->canBuy($isDestroy ? $entity->getDestroyPrice() : $entity->getPrice());
			}

			if ($HaveRessources && ($entity->isAvailable() || $isDestroy)) {
				$this->planet->metal 		-= $cost['metal'];
				$this->planet->crystal 		-= $cost['crystal'];
				$this->planet->deuterium 	-= $cost['deuterium'];
				$this->planet->update();

				$buildTime = $entity->getTime();

				if ($isDestroy) {
					$buildTime = ceil($buildTime / 2);
				}

				$buildItem->update([
					'time' => time(),
					'time_end' => time() + $buildTime
				]);

				$loop = false;

				if (config('game.log.buildings', false) == true) {
					LogHistory::query()->insert([
						'user_id' 			=> $this->user->id,
						'time' 				=> time(),
						'operation' 		=> ($isDestroy ? 2 : 1),
						'planet' 			=> $this->planet->id,
						'from_metal' 		=> $this->planet->metal + $cost['metal'],
						'from_crystal' 		=> $this->planet->crystal + $cost['crystal'],
						'from_deuterium' 	=> $this->planet->deuterium + $cost['deuterium'],
						'to_metal' 			=> $this->planet->metal,
						'to_crystal' 		=> $this->planet->crystal,
						'to_deuterium' 		=> $this->planet->deuterium,
						'build_id' 			=> $buildItem->object_id,
						'level' 			=> ($build['level'] + 1)
					]);
				}
			} else {
				if ($HaveNoMoreLevel) {
					$message = sprintf(__('main.sys_nomore_level'), __('main.tech.' . $buildItem->object_id));
				} elseif (!$HaveRessources) {
					$message = 'У вас недостаточно ресурсов чтобы начать строительство здания "' . __('main.tech.' . $buildItem->object_id) . '" на планете ' . $this->planet->name . ' ' . Helpers::BuildPlanetAdressLink($this->planet->toArray()) . '.<br>Вам необходимо ещё: <br>';

					if ($cost['metal'] > $this->planet->metal) {
						$message .= Format::number($cost['metal'] - $this->planet->metal) . ' металла<br>';
					}
					if ($cost['crystal'] > $this->planet->crystal) {
						$message .= Format::number($cost['crystal'] - $this->planet->crystal) . ' кристалла<br>';
					}
					if ($cost['deuterium'] > $this->planet->deuterium) {
						$message .= Format::number($cost['deuterium'] - $this->planet->deuterium) . ' дейтерия<br>';
					}
					if (isset($cost['energy']) && isset($this->planet->energy_max) && $cost['energy'] > $this->planet->energy_max) {
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

		if (!($this->user instanceof User)) {
			throw new ErrorException('Произошла внутренняя ошибка: Queue::checkTechQueue::check::User');
		}

		$result	= false;

		$buildItem = Models\Queue::query()->where('user_id', $this->user->id)
			->where('type', Models\Queue::TYPE_TECH)->first();

		if ($buildItem) {
			if ($buildItem->planet_id != $this->planet->id) {
				$planet = Planet::query()->find((int) $buildItem->planet_id);

				if ($planet) {
					$planet->assignUser($this->user);
				}
			} else {
				$planet = $this->planet;
			}

			if (!$planet) {
				throw new ErrorException('Произошла внутренняя ошибка: Queue::checkTechQueue::check::Planet object not found');
			}

			if ($this->user->getTechLevel('intergalactic') > 0) {
				$planet->spaceLabs = $planet->getNetworkLevel();
			}

			$entity = new Entity\Research($buildItem->object_id, $buildItem->level, new Entity\Context($this->user, $planet));

			$buildTime = $entity->getTime();

			if ($buildItem->time + $buildTime != $buildItem->time_end) {
				$buildItem->update([
					'time_end' => $buildItem->time + $buildTime
				]);
			}

			if ($buildItem->time + $buildTime <= time() + 5) {
				$this->user->setTech($buildItem->object_id, $buildItem->level);

				if (!$this->deleteInQueue($buildItem->id)) {
					$buildItem->delete();
				}

				if ($planet->id == $this->planet->id) {
					$this->loadQueue();
				}

				if (config('game.log.research', false) == true) {
					LogHistory::query()->insert([
						'user_id' 			=> $this->user->id,
						'time' 				=> time(),
						'operation' 		=> 8,
						'planet' 			=> $planet->id,
						'from_metal' 		=> $planet->metal,
						'from_crystal' 		=> $planet->crystal,
						'from_deuterium' 	=> $planet->deuterium,
						'to_metal' 			=> $planet->metal,
						'to_crystal' 		=> $planet->crystal,
						'to_deuterium' 		=> $planet->deuterium,
						'build_id' 			=> $buildItem->object_id,
						'level' 			=> $buildItem->level
					]);
				}

				$result	= true;
			}

			$this->user->update();
		}

		return $result;
	}

	public function checkUnitQueue()
	{
		if ($this->getCount(self::TYPE_SHIPYARD)) {
			$buildQueue = $this->get(self::TYPE_SHIPYARD);

			$MissilesSpace = ($this->planet->getBuildLevel('missile_facility') * 10) - ($this->planet->getUnitCount('interceptor_misil') + (2 * $this->planet->getUnitCount('interplanetary_misil')));

			$max = [];
			$buildTypes = Vars::getItemsByType([Vars::ITEM_TYPE_FLEET, Vars::ITEM_TYPE_DEFENSE]);

			foreach ($buildTypes as $id) {
				$price = Vars::getItemPrice($id);

				if (isset($price['max'])) {
					$max[$id] = $this->planet->getUnitCount($id);
				}
			}

			$builded = 0;

			foreach ($buildQueue as &$item) {
				if ($item->object_id == 502 || $item->object_id == 503) {
					if ($item->object_id == 502) {
						if ($item->level > $MissilesSpace) {
							$item->level = $MissilesSpace;
						} else {
							$MissilesSpace -= $item->level;
						}
					} else {
						if ($item->level > floor($MissilesSpace / 2)) {
							$item->level = floor($MissilesSpace / 2);
						} else {
							$MissilesSpace -= $item->level;
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

			unset($item);

			$context = new Entity\Context($this->user, $this->planet);

			foreach ($buildQueue as $i => $item) {
				if (!in_array($item->object_id, $buildTypes)) {
					continue;
				}

				if (Vars::getItemType($item->object_id) === Vars::ITEM_TYPE_DEFENSE) {
					$entity = new Entity\Defence($item->object_id, 1, $context);
				} else {
					$entity = new Entity\Fleet($item->object_id, 1, $context);
				}

				$buildTime = $entity->getTime();

				while ($item->time + $buildTime < time()) {
					$item->time += $buildTime;

					$builded++;
					$this->planet->setUnit($item->object_id, 1, true);
					$item->level--;

					if ($item->level <= 0) {
						if (!$this->deleteInQueue($item->id)) {
							$item->delete();
						}

						if (isset($buildQueue[$i + 1])) {
							$buildQueue[$i + 1]->time = $item->time;
						}

						break;
					}
				}

				$this->planet->update();

				if ($item->level > 0) {
					$item->time_end = $item->time + $buildTime;
					$item->update();

					break;
				}
			}

			return $builded > 0;
		}

		return false;
	}
}
