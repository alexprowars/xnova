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
	private $queue = [];

	const QUEUE_TYPE_BUILDING = 'building';
	const QUEUE_TYPE_RESEARCH = 'research';
	const QUEUE_TYPE_SHIPYARD = 'shipyard';
	/**
	 * @var Models\User user
	 */
	private $user;
	/**
	 * @var Models\Planet
	 */
	private $planet;

	private $registry;
	private $config;

	public function __construct($queue = '')
	{
		$this->loadQueue($queue);
	}

	public function loadQueue ($queue)
	{
		if (is_string($queue))
			$this->queue = json_decode($queue, true);
		else
			$this->queue = $queue;

		if (!is_array($this->queue))
			$this->queue = [];
	}
	
	public function setUserObject (Models\User $user)
	{
		$this->user = $user;
	}
	
	public function setPlanetObject (Models\Planet $planet)
	{
		$this->planet = $planet;

		$this->registry = Di::getDefault()->getShared('registry');
		$this->config 	= Di::getDefault()->getShared('config');
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
		$type = Vars::getItemType($elementId);

		if ($type == Vars::ITEM_TYPE_BUILING)
		{
			(new Build($this))->add($elementId, $destroy);

			$result = $this->nextBuildingQueue();

			if (!$result)
				$this->saveQueue();
		}
		elseif ($type == Vars::ITEM_TYPE_TECH)
			(new Tech($this))->add($elementId);
		elseif ($type == Vars::ITEM_TYPE_FLEET || $type == Vars::ITEM_TYPE_DEFENSE)
			(new Unit($this))->add($elementId, $count);
	}

	public function delete($elementId, $listId = 0)
	{
		$type = Vars::getItemType($elementId);

		if ($type == Vars::ITEM_TYPE_BUILING)
		{
			(new Build($this))->delete($listId);

			$result = $this->nextBuildingQueue();

			if (!$result)
				$this->saveQueue();
		}
		elseif ($type == Vars::ITEM_TYPE_TECH)
			(new Tech($this))->delete($elementId);
	}

	public function get($type = '')
	{
		if (!$type)
			return $this->queue;
		elseif (isset($this->queue[$type]))
			return $this->queue[$type];
		else
			return [];
	}

	public function is($type)
	{
		return isset($this->queue[$type]);
	}

	public function set($type, $queue)
	{
		if ($type === false)
			$this->queue = $queue;
		else
		{
			if (!isset($this->queue[$type]))
				$this->queue[$type] = [];

			if (!isset($queue['i']))
				$this->queue[$type] = $queue;
			else
				$this->queue[$type][] = $queue;
		}
	}

	public function getCount($queueType = '')
	{
		if (!$queueType)
		{
			$cnt = 0;

			foreach ($this->getTypes() AS $type)
			{
				$cnt += $this->getCount($type);
			}

			return $cnt;
		}
		elseif (isset($this->queue[$queueType]))
			return count($this->queue[$queueType]);
		else
			return 0;
	}

	public function getTypes ()
	{
		return Array(self::QUEUE_TYPE_BUILDING, self::QUEUE_TYPE_RESEARCH, self::QUEUE_TYPE_SHIPYARD);
	}

	public function saveQueue ()
	{
		if (!is_object($this->planet))
			return;

		$this->checkQueue();
		$this->planet->queue = json_encode($this->get());
		$this->planet->update();
	}

	public function checkQueue ()
	{
		$types = $this->getTypes();
		
		foreach ($this->queue AS $key => $value)
		{
			if (!in_array((string) $key, $types))
				unset($this->queue[$key]);
			elseif (!count($value))
				unset($this->queue[$key]);
		}
	}

	public function update ($time = 0)
	{
		if (!($this->planet instanceof Models\Planet))
			throw new ErrorException('Произошла внутренняя ошибка: Queue::update::check::Planet');

		if (!($this->user instanceof Models\User))
			throw new ErrorException('Произошла внутренняя ошибка: Queue::update::check::User');

		$result = false;

		$buildingsCount = $this->getCount(self::QUEUE_TYPE_BUILDING);

		if ($buildingsCount)
		{
			for ($i = 0; $i < $buildingsCount; $i++)
			{
				if ($this->checkBuildQueue())
				{
					if (!$this->planet->planet_updated)
						$this->planet->resourceUpdate();

					$this->planet->resourceProductions();

					$this->nextBuildingQueue();
					$result = true;
				}
				else
					break;
			}
		}

		if ($this->getCount(self::QUEUE_TYPE_RESEARCH) > 0 && $this->user->b_tech_planet == 0)
		{
			$this->user->b_tech_planet = $this->planet->id;
			$this->user->update();
		}

		if ($this->user->b_tech_planet)
			$result = true;

		$this->checkTechQueue();
		$this->checkUnitQueue($time);

		return $result;
	}

	private function checkBuildQueue ()
	{
		if ($this->getCount(self::QUEUE_TYPE_BUILDING))
		{
			$QueueArray = $this->get(self::QUEUE_TYPE_BUILDING);

			$BuildArray = $QueueArray[0];
			$Element = $BuildArray['i'];

			array_shift($QueueArray);

			$ForDestroy = ($BuildArray['d'] == 1);

			if ($BuildArray['e'] <= time())
			{
				$Needed = Building::getBuildingPrice($this->user, $this->planet, $Element, true, $ForDestroy);
				$Units = $Needed['metal'] + $Needed['crystal'] + $Needed['deuterium'];

				$XP = 0;

				if (in_array($Element, $this->registry->reslist['build_exp']))
				{
					if (!$ForDestroy)
						$XP += floor($Units / $this->config->game->get('buildings_exp_mult', 1000));
					else
						$XP -= floor($Units / $this->config->game->get('buildings_exp_mult', 1000));
				}

				$build = $this->planet->getBuild($Element);

				if (!$ForDestroy)
				{
					$this->planet->field_current++;
					$this->planet->setBuild($Element, $build['level'] + 1);
				}
				else
				{
					$this->planet->field_current--;
					$this->planet->setBuild($Element, $build['level'] - 1);
				}

				$NewQueue = $this->get();
				$NewQueue[self::QUEUE_TYPE_BUILDING] = $QueueArray;

				$this->loadQueue($NewQueue);
				$this->saveQueue();

				if ($XP != 0 && $this->user->lvl_minier < $this->config->game->get('level.max_ind', 100))
				{
					$this->user->xpminier += $XP;

					if ($this->user->xpminier < 0)
						$this->user->xpminier = 0;

					$this->user->update();
				}

				return true;
			}
			else
				return false;
		}

		return false;
	}

	private function nextBuildingQueue ()
	{
		if ($this->getCount(self::QUEUE_TYPE_BUILDING))
		{
			$QueueArray = $this->get(self::QUEUE_TYPE_BUILDING);

			if ($QueueArray[0]['s'] > 0)
				return false;

			$Loop = true;

			while ($Loop)
			{
				$ListIDArray = $QueueArray[0];

				$HaveNoMoreLevel = false;

				$build = $this->planet->getBuild($ListIDArray['i']);

				if (!$build)
				{
					array_shift($QueueArray);

					if (count($QueueArray) == 0)
						$Loop = false;

					continue;
				}

				$isDestroy = ($ListIDArray['d'] == 1);

				if ($isDestroy && $build['level'] == 0)
				{
					$HaveRessources = false;
					$HaveNoMoreLevel = true;
				}
				else
					$HaveRessources = Building::isElementBuyable($this->user, $this->planet, $ListIDArray['i'], true, $isDestroy);

				if ($HaveRessources && (Building::isTechnologieAccessible($this->user, $this->planet, $ListIDArray['i']) || $isDestroy))
				{
					$Needed = Building::getBuildingPrice($this->user, $this->planet, $ListIDArray['i'], true, $isDestroy);

					$this->planet->metal 		-= $Needed['metal'];
					$this->planet->crystal 		-= $Needed['crystal'];
					$this->planet->deuterium 	-= $Needed['deuterium'];

					$QueueArray[0]['s'] = time();

					$Loop = false;

					if ($this->config->log->get('buildings', false) == true)
					{
						Di::getDefault()->getShared('db')->insertAsDict('game_log_history',
						[
							'user_id' 			=> $this->user->id,
							'time' 				=> time(),
							'operation' 		=> ($isDestroy ? 2 : 1),
							'planet' 			=> $this->planet->id,
							'from_metal' 		=> $this->planet->metal + $Needed['metal'],
							'from_crystal' 		=> $this->planet->crystal + $Needed['crystal'],
							'from_deuterium' 	=> $this->planet->deuterium + $Needed['deuterium'],
							'to_metal' 			=> $this->planet->metal,
							'to_crystal' 		=> $this->planet->crystal,
							'to_deuterium' 		=> $this->planet->deuterium,
							'build_id' 			=> $ListIDArray['i'],
							'level' 			=> ($build['level'] + 1)
						]);
					}
				}
				else
				{
					if ($HaveNoMoreLevel)
						$Message = sprintf(_getText('sys_nomore_level'), _getText('tech', $ListIDArray['i']));
					elseif (!$HaveRessources)
					{
						$Needed = Building::getBuildingPrice($this->user, $this->planet, $ListIDArray['i'], true, $isDestroy);

						$Message = 'У вас недостаточно ресурсов чтобы начать строительство здания "' . _getText('tech', $ListIDArray['i']) . '" на планете '.$this->planet->name.' '.Helpers::BuildPlanetAdressLink($this->planet->toArray()).'.<br>Вам необходимо ещё: <br>';

						if ($Needed['metal'] > $this->planet->metal)
							$Message .= Format::number($Needed['metal'] - $this->planet->metal) . ' металла<br>';
						if ($Needed['crystal'] > $this->planet->crystal)
							$Message .= Format::number($Needed['crystal'] - $this->planet->crystal) . ' кристалла<br>';
						if ($Needed['deuterium'] > $this->planet->deuterium)
							$Message .= Format::number($Needed['deuterium'] - $this->planet->deuterium) . ' дейтерия<br>';
						if (isset($Needed['energy']) && isset($this->planet->energy_max) && $Needed['energy'] > $this->planet->energy_max)
							$Message .= Format::number($Needed['energy'] - $this->planet->energy_max) . ' энергии<br>';
					}

					if (isset($Message))
						User::sendMessage($this->user->id, 0, 0, 99, _getText('sys_buildlist'), $Message);

					array_shift($QueueArray);

					if (count($QueueArray) == 0)
						$Loop = false;
				}
			}

			$this->checkQueue();
			$newQueue = $this->get();

			$BuildEndTime = time();

			foreach ($QueueArray as &$ListIDArray)
			{
				$ListIDArray['t'] = Building::getBuildingTime($this->user, $this->planet, $ListIDArray['i']);

				if ($ListIDArray['d'])
					$ListIDArray['t'] = ceil($ListIDArray['t'] / 2);

				$BuildEndTime += $ListIDArray['t'];
				$ListIDArray['e'] = $BuildEndTime;
			}

			unset($ListIDArray);

			$newQueue[self::QUEUE_TYPE_BUILDING] = $QueueArray;
			$newQueue = json_encode($newQueue);

			$this->loadQueue($newQueue);
			$this->saveQueue();
		}

		return true;
	}

	public function checkTechQueue ()
	{
		if (!($this->planet instanceof Models\Planet))
			throw new ErrorException('Произошла внутренняя ошибка: Queue::checkTechQueue::check::Planet');

		if (!($this->user instanceof Models\User))
			throw new ErrorException('Произошла внутренняя ошибка: Queue::checkTechQueue::check::User');

		$Result['planet'] 	= false;
		$Result['working'] 	= false;

		if ($this->user->b_tech_planet != 0)
		{
			if ($this->user->b_tech_planet != $this->planet->id)
				$ThePlanet = Models\Planet::findFirst($this->user->b_tech_planet);
			else
				$ThePlanet = $this->planet;

			$queueManager 	= new Queue($ThePlanet->queue);
			$queueArray 	= $queueManager->get($queueManager::QUEUE_TYPE_RESEARCH);

			if (count($queueArray))
			{
				if ($queueArray[0]['e'] <= time())
				{
					$this->user->b_tech_planet = 0;
					$this->user->setTech($queueArray[0]['i'], $this->user->getTechLevel($queueArray[0]['i']) + 1);

					$queueManager->checkQueue();
					$newQueue = $queueManager->get();
					unset($newQueue[$queueManager::QUEUE_TYPE_RESEARCH]);

					$ThePlanet->queue = json_encode($newQueue);
					$ThePlanet->update();

					if ($this->user->b_tech_planet == $this->planet->id)
						$this->loadQueue($newQueue);
				}
				else
				{
					$Result['planet'] 	= $ThePlanet;
					$Result['working'] 	= true;
				}
			}
			else
				$this->user->b_tech_planet = 0;

			$this->user->update();
		}

		return $Result;
	}

	private function checkUnitQueue ($ProductionTime)
	{
		if ($this->getCount(self::QUEUE_TYPE_SHIPYARD))
		{
			$queueArray = $this->get();
			$BuildQueue = $this->get(self::QUEUE_TYPE_SHIPYARD);

			$this->planet->b_hangar = $BuildQueue[0]['s'];
			$this->planet->b_hangar += $ProductionTime;

			$MissilesSpace = ($this->planet->getBuildLevel('missile_facility') * 10) - ($this->planet->getUnitCount('interceptor_misil') + (2 * $this->planet->getUnitCount('interplanetary_misil')));

			$max = [];
			$buildTypes = Vars::getItemsByType([Vars::ITEM_TYPE_FLEET, Vars::ITEM_TYPE_DEFENSE]);

			foreach ($buildTypes as $id)
			{
				$price = Vars::getItemPrice($id);

				if (isset($price['max']) && $this->planet->getUnitCount($id) > 0)
					$max[$id] = $this->planet->getUnitCount($id);
			}

			$BuildArray = [];
			$Builded = 0;

			foreach ($BuildQueue as $Node => $Item)
			{
				if ($Item['i'] == 502 || $Item['i'] == 503)
				{
					if ($Item['i'] == 502)
					{
						if ($Item['l'] > $MissilesSpace)
							$Item['l'] = $MissilesSpace;
						else
							$MissilesSpace -= $Item['l'];
					}
					else
					{
						if ($Item['l'] > floor($MissilesSpace / 2))
							$Item['l'] = floor($MissilesSpace / 2);
						else
							$MissilesSpace -= $Item['l'];
					}
				}

				$price = Vars::getItemPrice($Item['i']);

				if (isset($price['max']))
				{
					if ($Item['l'] > $price['max'])
						$Item['l'] = $price['max'];

					if ($max[$Item['i']] + $Item['l'] > $price['max'])
						$Item['l'] = $price['max'] - $max[$Item['i']];

					if ($Item['l'] > 0)
						$max[$Item['i']] += $Item['l'];
					else
						$Item['l'] = 0;
				}

				$BuildArray[$Node] = [$Item['i'], $Item['l'], Building::getBuildingTime($this->user, $this->planet, $Item['i'])];
			}

			$UnFinished = false;

			$queueArray[self::QUEUE_TYPE_SHIPYARD] = [];

			foreach ($BuildArray as list($Element, $Count, $BuildTime))
			{
				if (!in_array($Element, $buildTypes))
					continue;

				while ($this->planet->b_hangar >= $BuildTime && !$UnFinished)
				{
					$this->planet->b_hangar -= $BuildTime;
					$Builded++;
					$this->planet->setUnit($Element, 1, true);
					$Count--;

					if ($Count <= 0)
						break;
					elseif ($this->planet->b_hangar < $BuildTime)
						$UnFinished = true;
				}

				if ($Count > 0)
				{
					$UnFinished = true;

					$queueArray[self::QUEUE_TYPE_SHIPYARD][] = [
						'i' => $Element,
						'l' => $Count,
						't' => 0,
						's' => count($queueArray[self::QUEUE_TYPE_SHIPYARD]) == 0 ? $this->planet->b_hangar : 0,
						'e' => 0
					];
				}
			}

			if (!count($queueArray[self::QUEUE_TYPE_SHIPYARD]))
				unset($queueArray[self::QUEUE_TYPE_SHIPYARD]);

			$this->loadQueue($queueArray);
			$this->saveQueue();

			return $Builded;
		}

		return 0;
	}
}