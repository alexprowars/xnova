<?php

namespace Xnova;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Exceptions\ErrorException;
use Phalcon\Di;
use Xnova\Queue\Build;
use Xnova\Queue\Tech;
use Xnova\Queue\Unit;

class Queue
{
	/** @var Models\Queue[]|bool */
	private $queue = false;

	const TYPE_BUILDING = Models\Queue::TYPE_BUILD;
	const TYPE_RESEARCH = Models\Queue::TYPE_TECH;
	const TYPE_SHIPYARD = Models\Queue::TYPE_UNIT;
	/** @var Models\User user */
	private $user;
	/** @var Models\Planet planet */
	private $planet;

	public function __construct ($user, $planet = null)
	{
		if ($user instanceof Models\User)
			$this->user = $user;
		else
			$this->user = Models\User::findFirst((int) $user);

		if ($planet instanceof Models\Planet)
			$this->planet = $planet;

		$this->loadQueue();
	}

	public function loadQueue ()
	{
		$this->queue = [];

		$query = Di::getDefault()->getShared('modelsManager')
			->createBuilder()
			->from('Xnova\Models\Queue')
			->orderBy('id ASC')
			->where('user_id = :user:', ['user' => $this->user->getId()]);

		if ($this->planet)
			$query->andWhere('planet_id = :planet:', ['planet' => $this->planet->id]);

		$items = $query->getQuery()->execute();

		foreach ($items as $item)
			$this->queue[] = $item;
	}
	
	public function setPlanet (Models\Planet $planet)
	{
		$this->planet = $planet;
	}

	public function getPlanet ()
	{
		return $this->planet;
	}

	public function getUser ()
	{
		return $this->user;
	}

	public function add($elementId, $count = 1, $destroy = false)
	{
		$elementId = (int) $elementId;

		$type = Vars::getItemType($elementId);

		if ($type == Vars::ITEM_TYPE_BUILING)
			(new Build($this))->add($elementId, $destroy);
		elseif ($type == Vars::ITEM_TYPE_TECH)
			(new Tech($this))->add($elementId);
		elseif ($type == Vars::ITEM_TYPE_FLEET || $type == Vars::ITEM_TYPE_DEFENSE)
			(new Unit($this))->add($elementId, $count);
	}

	public function delete($elementId, $listId = 0)
	{
		$elementId = (int) $elementId;

		$type = Vars::getItemType($elementId);

		if ($type == Vars::ITEM_TYPE_BUILING)
			(new Build($this))->delete($listId);
		elseif ($type == Vars::ITEM_TYPE_TECH)
			(new Tech($this))->delete($elementId);
	}

	public function get($type = '')
	{
		if (!is_array($this->queue))
			$this->loadQueue();

		if (!$type)
			return $this->queue;
		elseif (in_array($type, [self::TYPE_BUILDING, self::TYPE_RESEARCH, self::TYPE_SHIPYARD]))
		{
			$r = [];

			foreach ($this->queue as $item)
			{
				if ($item->type == $type)
					$r[] = $item;
			}

			return $r;
		}
		else
			return [];
	}

	public function getCount($queueType = '')
	{
		if (!is_array($this->queue))
			$this->loadQueue();

		if (!$queueType)
			return count($this->queue);
		elseif (in_array($queueType, [self::TYPE_BUILDING, self::TYPE_RESEARCH, self::TYPE_SHIPYARD]))
		{
			$cnt = 0;

			foreach ($this->queue as $item)
			{
				if ($item->type == $queueType)
					$cnt++;
			}

			return $cnt;
		}
		else
			return 0;
	}

	public function getTypes ()
	{
		return [self::TYPE_BUILDING, self::TYPE_RESEARCH, self::TYPE_SHIPYARD];
	}

	public function deleteInQueue ($id)
	{
		foreach ($this->queue as $i => $item)
		{
			if ($item->id == $id)
			{
				if ($item->delete())
				{
					unset($this->queue[$i]);

					return true;
				}

				break;
			}
		}

		return false;
	}

	public function update ()
	{
		if (!($this->planet instanceof Models\Planet))
			throw new ErrorException('Произошла внутренняя ошибка: Queue::update::check::Planet');

		if (!($this->user instanceof Models\User))
			throw new ErrorException('Произошла внутренняя ошибка: Queue::update::check::User');

		$buildingsCount = $this->getCount(self::TYPE_BUILDING);

		if ($buildingsCount)
		{
			$this->nextBuildingQueue();

			for ($i = 0; $i < $buildingsCount; $i++)
			{
				if ($this->checkBuildQueue())
				{
					if (!$this->planet->planet_updated)
						$this->planet->resourceUpdate();

					$this->planet->resourceProductions();

					$this->nextBuildingQueue();
				}
				else
					break;
			}
		}

		$this->checkTechQueue();
		$this->checkUnitQueue();

		return true;
	}

	private function checkBuildQueue ()
	{
		$queueArray = $this->get(self::TYPE_BUILDING);

		if (!count($queueArray))
			return false;

		$buildItem = $queueArray[0];

		$build = $this->planet->getBuild($buildItem->object_id);
		$isDestroy = $buildItem->operation == Models\Queue::OPERATION_DESTROY;

		$buildTime = Building::getBuildingTime($this->user, $this->planet, $buildItem->object_id, $build['level']);

		if ($isDestroy)
			$buildTime = ceil($buildTime / 2);

		if ($buildItem->time + $buildTime != $buildItem->time_end)
		{
			$buildItem->update([
				'time_end' => $buildItem->time + $buildTime
			]);
		}

		if ($buildItem->time + $buildTime <= time() + 5)
		{
			$config = Di::getDefault()->getShared('config');
			$registry = Di::getDefault()->getShared('registry');

			$cost = Building::getBuildingPrice($this->user, $this->planet, $buildItem->object_id, true, $isDestroy);
			$units = $cost['metal'] + $cost['crystal'] + $cost['deuterium'];

			$xp = 0;

			if (in_array($buildItem->object_id, $registry->reslist['build_exp']))
			{
				if (!$isDestroy)
					$xp += floor($units / $config->game->get('buildings_exp_mult', 1000));
				else
					$xp -= floor($units / $config->game->get('buildings_exp_mult', 1000));
			}

			if (!$isDestroy)
			{
				$this->planet->field_current++;
				$this->planet->setBuild($buildItem->object_id, $build['level'] + 1);
			}
			else
			{
				$this->planet->field_current--;
				$this->planet->setBuild($buildItem->object_id, $build['level'] - 1);
			}

			if (!$this->deleteInQueue($buildItem->id))
				$buildItem->delete();

			if ($xp != 0 && $this->user->lvl_minier < $config->game->get('level.max_ind', 100))
			{
				$this->user->xpminier += $xp;

				if ($this->user->xpminier < 0)
					$this->user->xpminier = 0;

				$this->user->update();
			}

			$config = Di::getDefault()->getShared('config');

			if ($config->log->get('buildings', false) == true)
			{
				Di::getDefault()->getShared('db')->insertAsDict('game_log_history', [
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

	public function nextBuildingQueue ()
	{
		$queueArray = $this->get(self::TYPE_BUILDING);

		if (!count($queueArray) || $queueArray[0]->time > 0)
			return false;

		$loop = true;

		while ($loop)
		{
			$buildItem = $queueArray[0];

			$HaveNoMoreLevel = false;

			$build = $this->planet->getBuild($buildItem->object_id);

			if (!$build)
			{
				array_shift($queueArray);

				if (!$this->deleteInQueue($buildItem->id))
					$buildItem->delete();

				if (!count($queueArray))
					$loop = false;

				continue;
			}

			$isDestroy = $buildItem->operation == $buildItem::OPERATION_DESTROY;

			if ($isDestroy && $build['level'] == 0)
			{
				$HaveRessources = false;
				$HaveNoMoreLevel = true;
			}
			else
				$HaveRessources = Building::isElementBuyable($this->user, $this->planet, $buildItem->object_id, true, $isDestroy);

			if ($HaveRessources && (Building::isTechnologieAccessible($this->user, $this->planet, $buildItem->object_id) || $isDestroy))
			{
				$cost = Building::getBuildingPrice($this->user, $this->planet, $buildItem->object_id, true, $isDestroy);

				$this->planet->metal 		-= $cost['metal'];
				$this->planet->crystal 		-= $cost['crystal'];
				$this->planet->deuterium 	-= $cost['deuterium'];
				$this->planet->update();

				$buildTime = Building::getBuildingTime($this->user, $this->planet, $buildItem->object_id);

				if ($isDestroy)
					$buildTime = ceil($buildTime / 2);

				$buildItem->update([
					'time' => time(),
					'time_end' => time() + $buildTime
				]);

				$loop = false;

				$config = Di::getDefault()->getShared('config');

				if ($config->log->get('buildings', false) == true)
				{
					Di::getDefault()->getShared('db')->insertAsDict('game_log_history', [
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
			}
			else
			{
				if ($HaveNoMoreLevel)
					$message = sprintf(_getText('sys_nomore_level'), _getText('tech', $buildItem->object_id));
				elseif (!$HaveRessources)
				{
					$cost = Building::getBuildingPrice($this->user, $this->planet, $buildItem->object_id, true, $isDestroy);

					$message = 'У вас недостаточно ресурсов чтобы начать строительство здания "' . _getText('tech', $buildItem->object_id) . '" на планете '.$this->planet->name.' '.Helpers::BuildPlanetAdressLink($this->planet->toArray()).'.<br>Вам необходимо ещё: <br>';

					if ($cost['metal'] > $this->planet->metal)
						$message .= Format::number($cost['metal'] - $this->planet->metal) . ' металла<br>';
					if ($cost['crystal'] > $this->planet->crystal)
						$message .= Format::number($cost['crystal'] - $this->planet->crystal) . ' кристалла<br>';
					if ($cost['deuterium'] > $this->planet->deuterium)
						$message .= Format::number($cost['deuterium'] - $this->planet->deuterium) . ' дейтерия<br>';
					if (isset($cost['energy']) && isset($this->planet->energy_max) && $cost['energy'] > $this->planet->energy_max)
						$message .= Format::number($cost['energy'] - $this->planet->energy_max) . ' энергии<br>';
				}

				if (isset($message))
					User::sendMessage($this->user->id, 0, 0, 99, _getText('sys_buildlist'), $message);

				array_shift($queueArray);

				if (!$this->deleteInQueue($buildItem->id))
					$buildItem->delete();

				if (!count($queueArray))
					$loop = false;
			}
		}

		$this->loadQueue();

		return true;
	}

	public function checkTechQueue ()
	{
		if (!($this->planet instanceof Models\Planet))
			throw new ErrorException('Произошла внутренняя ошибка: Queue::checkTechQueue::check::Planet');

		if (!($this->user instanceof Models\User))
			throw new ErrorException('Произошла внутренняя ошибка: Queue::checkTechQueue::check::User');

		$result	= false;

		$buildItem = Models\Queue::findFirst([
			'conditions' => 'user_id = :user: AND type = :type:',
			'bind' => [
				'user' => $this->user->id,
				'type' => Models\Queue::TYPE_TECH
			]
		]);

		if ($buildItem)
		{
			if ($buildItem->planet_id != $this->planet->id)
			{
				$planet = Models\Planet::findFirst((int) $buildItem->planet_id);

				if ($planet)
					$planet->assignUser($this->user);
			}
			else
				$planet = $this->planet;

			if (!$planet)
				throw new ErrorException('Произошла внутренняя ошибка: Queue::checkTechQueue::check::Planet object not found');

			if ($this->user->getTechLevel('intergalactic') > 0)
				$planet->spaceLabs = $planet->getNetworkLevel();

			$buildTime = Building::getBuildingTime($this->user, $planet, $buildItem->object_id);

			if ($buildItem->time + $buildTime != $buildItem->time_end)
			{
				$buildItem->update([
					'time_end' => $buildItem->time + $buildTime
				]);
			}

			if ($buildItem->time + $buildTime <= time() + 5)
			{
				$this->user->setTech($buildItem->object_id, $buildItem->level);

				if (!$this->deleteInQueue($buildItem->id))
					$buildItem->delete();

				if ($planet->id == $this->planet->id)
					$this->loadQueue();

				$config = Di::getDefault()->getShared('config');

				if ($config->log->get('research', false) == true)
				{
					Di::getDefault()->getShared('db')->insertAsDict('game_log_history', [
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

	private function checkUnitQueue ()
	{
		if ($this->getCount(self::TYPE_SHIPYARD))
		{
			$buildQueue = $this->get(self::TYPE_SHIPYARD);

			$MissilesSpace = ($this->planet->getBuildLevel('missile_facility') * 10) - ($this->planet->getUnitCount('interceptor_misil') + (2 * $this->planet->getUnitCount('interplanetary_misil')));

			$max = [];
			$buildTypes = Vars::getItemsByType([Vars::ITEM_TYPE_FLEET, Vars::ITEM_TYPE_DEFENSE]);

			foreach ($buildTypes as $id)
			{
				$price = Vars::getItemPrice($id);

				if (isset($price['max']))
					$max[$id] = $this->planet->getUnitCount($id);
			}

			$builded = 0;

			foreach ($buildQueue as &$item)
			{
				if ($item->object_id == 502 || $item->object_id == 503)
				{
					if ($item->object_id == 502)
					{
						if ($item->level > $MissilesSpace)
							$item->level = $MissilesSpace;
						else
							$MissilesSpace -= $item->level;
					}
					else
					{
						if ($item->level > floor($MissilesSpace / 2))
							$item->level = floor($MissilesSpace / 2);
						else
							$MissilesSpace -= $item->level;
					}
				}

				$price = Vars::getItemPrice($item->object_id);

				if (isset($price['max']))
				{
					if ($item->level > $price['max'])
						$item->level = $price['max'];

					if ($max[$item->object_id] + $item->level > $price['max'])
						$item->level = $price['max'] - $max[$item->object_id];

					if ($item->level > 0)
						$max[$item->object_id] += $item->level;
					else
						$item->level = 0;
				}
			}

			unset($item);

			foreach ($buildQueue as $i => $item)
			{
				if (!in_array($item->object_id, $buildTypes))
					continue;

				$buildTime = Building::getBuildingTime($this->user, $this->planet, $item->object_id);

				while ($item->time + $buildTime < time())
				{
					$item->time += $buildTime;

					$builded++;
					$this->planet->setUnit($item->object_id, 1, true);
					$item->level--;

					if ($item->level <= 0)
					{
						if (!$this->deleteInQueue($item->id))
							$item->delete();

						if (isset($buildQueue[$i + 1]))
							$buildQueue[$i + 1]->time = $item->time;

						break;
					}
				}

				$this->planet->update();

				if ($item->level > 0)
				{
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