<?php

namespace Xnova;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Exceptions\ErrorException;
use Xnova\Models\Planet;
use Xnova\Models\User;
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
	 * @var \Xnova\Models\User user
	 */
	private $user;
	/**
	 * @var \Xnova\Models\Planet
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
	
	public function setUserObject (User $user)
	{
		$this->user = $user;
	}
	
	public function setPlanetObject (Planet $planet)
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
		if (in_array($elementId, $this->registry->reslist['build']))
			(new Build($this))->add($elementId, $destroy);
		elseif (in_array($elementId, $this->registry->reslist['tech']) || in_array($elementId, $this->registry->reslist['tech_f']))
			(new Tech($this))->add($elementId);
		elseif (in_array($elementId, $this->registry->reslist['fleet']) || in_array($elementId, $this->registry->reslist['defense']))
			(new Unit($this))->add($elementId, $count);
	}

	public function delete($elementId, $listId = 0)
	{
		if (in_array($elementId, $this->registry->reslist['build']))
			(new Build($this))->delete($listId);
		elseif (in_array($elementId, $this->registry->reslist['tech']) || in_array($elementId, $this->registry->reslist['tech_f']))
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
		if (!($this->planet instanceof Planet))
			throw new ErrorException('Произошла внутренняя ошибка: Queue::update::check::Planet');

		if (!($this->user instanceof User))
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
				$Needed = Building::GetBuildingPrice($this->user, $this->planet, $Element, true, $ForDestroy);
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
				return;

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

				$ForDestroy = ($ListIDArray['d'] == 1);

				if ($ForDestroy && $build['level'] == 0)
				{
					$HaveRessources = false;
					$HaveNoMoreLevel = true;
				}
				else
					$HaveRessources = Building::IsElementBuyable($this->user, $this->planet, $ListIDArray['i'], true, $ForDestroy);

				if ($HaveRessources && Building::IsTechnologieAccessible($this->user, $this->planet, $ListIDArray['i']))
				{
					$Needed = Building::GetBuildingPrice($this->user, $this->planet, $ListIDArray['i'], true, $ForDestroy);

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
							'operation' 		=> ($ForDestroy ? 2 : 1),
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
						$Needed = Building::GetBuildingPrice($this->user, $this->planet, $ListIDArray['i'], true, $ForDestroy);

						$Message = 'У вас недостаточно ресурсов чтобы начать строительство здания "' . _getText('tech', $ListIDArray['i']) . '" на планете '.$this->planet->name.' '.Helpers::BuildPlanetAdressLink($this->planet->toArray()).'.<br>Вам необходимо ещё: <br>';

						if ($Needed['metal'] > $this->planet->metal)
							$Message .= Helpers::pretty_number($Needed['metal'] - $this->planet->metal) . ' металла<br>';
						if ($Needed['crystal'] > $this->planet->crystal)
							$Message .= Helpers::pretty_number($Needed['crystal'] - $this->planet->crystal) . ' кристалла<br>';
						if ($Needed['deuterium'] > $this->planet->deuterium)
							$Message .= Helpers::pretty_number($Needed['deuterium'] - $this->planet->deuterium) . ' дейтерия<br>';
						if (isset($Needed['energy_max']) && isset($this->planet->energy_max) && $Needed['energy_max'] > $this->planet->energy_max)
							$Message .= Helpers::pretty_number($Needed['energy_max'] - $this->planet->energy_max) . ' энергии<br>';
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
				$ListIDArray['t'] = Building::GetBuildingTime($this->user, $this->planet, $ListIDArray['i']);

				if ($ListIDArray['d'])
					$ListIDArray['t'] = ceil($ListIDArray['t'] / 2);

				$BuildEndTime += $ListIDArray['t'];
				$ListIDArray['e'] = $BuildEndTime;
			}

			unset($ListIDArray);

			$newQueue[self::QUEUE_TYPE_BUILDING] = $QueueArray;
			$newQueue = json_encode($newQueue);

			if ($this->queue != $newQueue)
			{
				$this->loadQueue($newQueue);
				$this->saveQueue();
			}
		}
	}

	public function checkTechQueue ()
	{
		if (!($this->planet instanceof Planet))
			throw new ErrorException('Произошла внутренняя ошибка: Queue::checkTechQueue::check::Planet');

		if (!($this->user instanceof User))
			throw new ErrorException('Произошла внутренняя ошибка: Queue::checkTechQueue::check::User');

		$Result['planet'] 	= false;
		$Result['working'] 	= false;

		if ($this->user->b_tech_planet != 0)
		{
			if ($this->user->b_tech_planet != $this->planet->id)
				$ThePlanet = Planet::findFirst($this->user->b_tech_planet);
			else
				$ThePlanet = $this->planet;

			$queueManager 	= new Queue($ThePlanet->queue);
			$queueArray 	= $queueManager->get($queueManager::QUEUE_TYPE_RESEARCH);

			if (count($queueArray))
			{
				if ($queueArray[0]['e'] <= time())
				{
					$this->user->b_tech_planet = 0;
					$this->user->{$this->registry->resource[$queueArray[0]['i']]}++;

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

			$MissilesSpace = ($this->planet->getBuildLevel('missile_facility') * 10) - ($this->planet->interceptor_misil + (2 * $this->planet->interplanetary_misil));

			$max = [];

			foreach ($this->registry->pricelist as $id => $data)
			{
				if (isset($data['max']) && isset($this->{$this->registry->resource[$id]}))
					$max[$id] = $this->{$this->registry->resource[$id]};
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

				if (isset($this->registry->pricelist[$Item['i']]['max']))
				{
					if ($Item['l'] > $this->registry->pricelist[$Item['i']]['max'])
						$Item['l'] = $this->registry->pricelist[$Item['i']]['max'];

					if ($max[$Item['i']] + $Item['l'] > $this->registry->pricelist[$Item['i']]['max'])
						$Item['l'] = $this->registry->pricelist[$Item['i']]['max'] - $max[$Item['i']];

					if ($Item['l'] > 0)
						$max[$Item['i']] += $Item['l'];
					else
						$Item['l'] = 0;
				}

				$BuildArray[$Node] = [$Item['i'], $Item['l'], Building::GetBuildingTime($this->user, $this->planet, $Item['i'])];
			}

			$UnFinished = false;

			$queueArray[self::QUEUE_TYPE_SHIPYARD] = [];

			foreach ($BuildArray as list($Element, $Count, $BuildTime))
			{
				if (!isset($this->registry->resource[$Element]))
					continue;

				while ($this->planet->b_hangar >= $BuildTime && !$UnFinished)
				{
					$this->planet->b_hangar -= $BuildTime;
					$Builded++;
					$this->{$this->registry->resource[$Element]}++;
					$Count--;

					if ($Count <= 0)
						break;
					elseif ($this->planet->b_hangar < $BuildTime)
						$UnFinished = true;
				}

				if ($Count > 0)
				{
					$UnFinished = true;

					$queueArray[self::QUEUE_TYPE_SHIPYARD][] = ['i' => $Element, 'l' => $Count, 't' => 0, 's' => count($queueArray[self::QUEUE_TYPE_SHIPYARD]) == 0 ? $this->planet->b_hangar : 0, 'e' => 0];
				}
			}

			if (!count($queueArray[self::QUEUE_TYPE_SHIPYARD]))
				unset($queueArray[self::QUEUE_TYPE_SHIPYARD]);

			$this->queue = json_encode($queueArray);

			return $Builded;
		}

		return 0;
	}
}