<?php
namespace App;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use App\Models\Planet;
use App\Models\User;
use Phalcon\Di;

class Queue
{
	private $queue = [];

	const QUEUE_TYPE_BUILDING = 'building';
	const QUEUE_TYPE_RESEARCH = 'research';
	const QUEUE_TYPE_SHIPYARD = 'shipyard';
	/**
	 * @var \App\Models\User user
	 */
	private $user;
	/**
	 * @var \App\Models\Planet
	 */
	private $planet;

	private $storage;
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

		$this->storage 	= Di::getDefault()->getShared('storage');
		$this->config 	= Di::getDefault()->getShared('config');
	}

	public function add($elementId, $count = 1, $destroy = false)
	{
		if (in_array($elementId, $this->storage->reslist['build']))
			$this->addBuildingToQueue($elementId, $destroy);
		elseif (in_array($elementId, $this->storage->reslist['tech']) || in_array($elementId, $this->storage->reslist['tech_f']))
			$this->addTechToQueue($elementId);
		elseif (in_array($elementId, $this->storage->reslist['fleet']) || in_array($elementId, $this->storage->reslist['defense']))
			$this->addShipyardToQueue($elementId, $count);
	}

	public function delete($elementId, $listId = 0)
	{
		if (in_array($elementId, $this->storage->reslist['build']))
			$this->deleteBuildingInQueue($listId);
		elseif (in_array($elementId, $this->storage->reslist['tech']) || in_array($elementId, $this->storage->reslist['tech_f']))
			$this->deleteTechInQueue($elementId);
	}

	public function get($queueType = '')
	{
		if (!$queueType)
			return $this->queue;
		elseif (isset($this->queue[$queueType]))
			return $this->queue[$queueType];
		else
			return [];
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

	private function addBuildingToQueue ($elementId, $destroy = false)
	{
		$maxBuidSize = $this->config->game->maxBuildingQueue;
		if ($this->user->rpg_constructeur > time())
			$maxBuidSize += 2;

		$actualCount = $this->getCount(self::QUEUE_TYPE_BUILDING);

		if ($actualCount < $maxBuidSize)
			$queueID = $actualCount + 1;
		else
			$queueID = false;

		$currentMaxFields = $this->planet->getMaxFields();

		if ($this->planet->field_current < ($currentMaxFields - $actualCount) || $destroy)
		{
			if ($queueID > 1)
			{
				$inArray = 0;

				foreach ($this->queue[self::QUEUE_TYPE_BUILDING] AS $item)
				{
					if ($item['i'] == $elementId)
						$inArray++;
				}
			}
			else
				$inArray = 0;

			$ActualLevel = $this->planet->{$this->storage->resource[$elementId]};

			if (!$destroy)
			{
				$BuildLevel = $ActualLevel + 1 + $inArray;
				$this->planet->{$this->storage->resource[$elementId]} += $inArray;
				$BuildTime = Building::GetBuildingTime($this->user, $this->planet, $elementId);
				$this->planet->{$this->storage->resource[$elementId]} -= $inArray;
			}
			else
			{
				$BuildLevel = $ActualLevel - 1 + $inArray;
				$this->planet->{$this->storage->resource[$elementId]} -= $inArray;
				$BuildTime = Building::GetBuildingTime($this->user, $this->planet, $elementId) / 2;
				$this->planet->{$this->storage->resource[$elementId]} += $inArray;
			}

			if ($queueID == 1)
				$BuildEndTime = time() + $BuildTime;
			else
			{
				$queueArray = $this->get(self::QUEUE_TYPE_BUILDING);

				$PrevBuild = $queueArray[$actualCount - 1];
				$BuildEndTime = $PrevBuild['e'] + $BuildTime;
			}

			if (!isset($this->queue[self::QUEUE_TYPE_BUILDING]))
				$this->queue[self::QUEUE_TYPE_BUILDING] = [];

			$this->queue[self::QUEUE_TYPE_BUILDING][] = [
				'i' => $elementId,
				'l' => $BuildLevel,
				't' => 0,
				's' => 0,
				'e' => $BuildEndTime,
				'd' => $destroy ? 1 : 0
			];

			$this->saveQueue();
		}
	}

	private function deleteBuildingInQueue ($elementId)
	{
		if ($this->getCount(self::QUEUE_TYPE_BUILDING))
		{
			$queueArray 	= $this->get(self::QUEUE_TYPE_BUILDING);
			$ActualCount 	= count($queueArray);

			if (!isset($queueArray[$elementId]))
				return;

			$canceledArray = $queueArray[$elementId];

			$newQueue = $this->get();

			if ($ActualCount > 1)
			{
				unset($queueArray[$elementId]);

				$queueArray = array_values($queueArray);
				
				if ($elementId == 0)
					$BuildEndTime = time();
				else
					$BuildEndTime = $queueArray[0]['s'];
					
				foreach ($queueArray AS $i => &$listArray)
				{
					$listArray['t'] = Building::GetBuildingTime($this->user, $this->planet, $listArray['i']);

					if ($listArray['d'] == 1)
						$listArray['t'] = ceil($listArray['t'] / 2);

					$BuildEndTime += $listArray['t'];

					$listArray['e'] = $BuildEndTime;

					if ($canceledArray['i'] == $listArray['i'] && $elementId <= $i)
						$listArray['l']--;
				}

				unset($listArray);

				$newQueue[self::QUEUE_TYPE_BUILDING] = $queueArray;
			}
			else
				unset($newQueue[self::QUEUE_TYPE_BUILDING]);
				
			$this->planet->queue = json_encode($newQueue);

			if ($canceledArray['s'] > 0)
			{
				$cost = Building::GetBuildingPrice($this->user, $this->planet, $canceledArray['i'], true, ($canceledArray['d'] == 1));

				$this->planet->metal 		+= $cost['metal'];
				$this->planet->crystal 		+= $cost['crystal'];
				$this->planet->deuterium 	+= $cost['deuterium'];
			}

			$this->planet->update();
		}
	}

	public function setNextQueue ()
	{
		if ($this->getCount(self::QUEUE_TYPE_BUILDING))
		{
			$QueueArray = $this->get(self::QUEUE_TYPE_BUILDING);

			if ($QueueArray[0]['s'] > 0)
				return;

			/**
			 * @var $db \App\Database
			 */
			$db = Di::getDefault()->getShared('db');

			$Loop = true;

			while ($Loop)
			{
				$ListIDArray = $QueueArray[0];

				$HaveNoMoreLevel = false;

				$ForDestroy = ($ListIDArray['d'] == 1);

				if ($ForDestroy && $this->{$this->storage->resource[$ListIDArray['i']]} == 0)
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
						$db->insertAsDict('game_log_history',
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
							'level' 			=> ($this->{$this->storage->resource[$ListIDArray['i']]} + 1)
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

						$Message = 'У вас недостаточно ресурсов чтобы начать строительство здания ' . _getText('tech', $ListIDArray['i']) . '.<br>Вам необходимо ещё: <br>';
						if ($Needed['metal'] > $this->planet->metal)
							$Message .= Helpers::pretty_number($Needed['metal'] - $this->planet->metal) . ' металла<br>';
						if ($Needed['crystal'] > $this->planet->crystal)
							$Message .= Helpers::pretty_number($Needed['crystal'] - $this->planet->crystal) . ' кристалла<br>';
						if ($Needed['deuterium'] > $this->planet->deuterium)
							$Message .= Helpers::pretty_number($Needed['deuterium'] - $this->planet->deuterium) . ' дейтерия<br>';
						if (isset($Needed['energy_max']) && isset($this->energy_max) && $Needed['energy_max'] > $this->energy_max)
							$Message .= Helpers::pretty_number($Needed['energy_max'] - $this->energy_max) . ' энергии<br>';
					}

					if (isset($Message))
						Di::getDefault()->getShared('game')->sendMessage($this->user->id, 0, 0, 99, _getText('sys_buildlist'), $Message);

					array_shift($QueueArray);

					if (count($QueueArray) == 0)
						$Loop = false;
				}
			}

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
				$this->queue = $newQueue;

				$db->query("LOCK TABLES ".$this->planet->getSource()." WRITE");

				$this->planet->update();

				$db->query("UNLOCK TABLES");
			}
		}
	}

	private function addTechToQueue ($elementId)
	{
		$TechHandle = $this->planet->checkResearchQueue();

		if (!$TechHandle['working'])
		{
			$spaceLabs = [];

			if ($this->user->{$this->storage->resource[123]} > 0)
				$spaceLabs = $this->planet->getNetworkLevel();

			$this->planet->spaceLabs = $spaceLabs;

			if (Building::IsTechnologieAccessible($this->user, $this->planet, $elementId) && Building::IsElementBuyable($this->user, $this->planet, $elementId) && !(isset($this->storage->pricelist[$elementId]['max']) && $this->user->{$this->storage->resource[$elementId]} >= $this->storage->pricelist[$elementId]['max']))
			{
				$costs = Building::GetBuildingPrice($this->user, $this->planet, $elementId);

				$this->planet->metal 		-= $costs['metal'];
				$this->planet->crystal 		-= $costs['crystal'];
				$this->planet->deuterium 	-= $costs['deuterium'];

				$time = Building::GetBuildingTime($this->user, $this->planet, $elementId);

				$this->queue[self::QUEUE_TYPE_RESEARCH] = [[
					'i' => $elementId,
					'l' => ($this->user->{$this->storage->resource[$elementId]} + 1),
					't' => $time,
					's' => time(),
					'e' => time() + $time,
					'd' => 0
				]];

				$this->planet->queue = json_encode($this->queue);

				$this->planet->update();
				$this->user->update(['b_tech_planet' => $this->planet->id]);
			}
		}
	}

	private function deleteTechInQueue ($elementId, $listId = 0)
	{
		$TechHandle = $this->planet->checkResearchQueue();

		if (isset($this->queue[self::QUEUE_TYPE_RESEARCH][$listId]) && $TechHandle['working'] && $this->queue[self::QUEUE_TYPE_RESEARCH][$listId]['i'] == $elementId)
		{
			$nedeed = Building::GetBuildingPrice($this->user, $TechHandle['planet'], $elementId);

			$TechHandle['planet']->metal 		+= $nedeed['metal'];
			$TechHandle['planet']->crystal 		+= $nedeed['crystal'];
			$TechHandle['planet']->deuterium 	+= $nedeed['deuterium'];

			unset($this->queue[self::QUEUE_TYPE_RESEARCH][$listId]);

			if (isset($this->queue[self::QUEUE_TYPE_BUILDING]) && !count($this->queue[self::QUEUE_TYPE_BUILDING]))
				unset($this->queue[self::QUEUE_TYPE_BUILDING]);

			$TechHandle['planet']->queue = json_encode($this->queue);
			$TechHandle['planet']->update();

			$this->user->update(['b_tech_planet' => $this->planet->id]);
		}
	}

	private function addShipyardToQueue ($elementId, $count)
	{
		if (!Building::IsTechnologieAccessible($this->user, $this->planet, $elementId))
			return;

		$BuildArray = $this->get(self::QUEUE_TYPE_SHIPYARD);

		if ($elementId == 502 || $elementId == 503)
		{
			$Missiles[502] = $this->planet->{$this->storage->resource[502]};
			$Missiles[503] = $this->planet->{$this->storage->resource[503]};

			$MaxMissiles = $this->planet->{$this->storage->resource[44]} * 10;

			foreach ($BuildArray AS $item)
			{
				if (($item['i'] == 502 || $item['i'] == 503) && $item['l'] != 0)
					$Missiles[$item['i']] += $item['l'];
			}
		}

		if (isset($this->storage->pricelist[$elementId]['max']))
		{
			$total = $this->planet->{$this->storage->resource[$elementId]};

			if (isset($BuildArray[$elementId]))
				$total += $BuildArray[$elementId];

			$count = min($count, max(($this->storage->pricelist[$elementId]['max'] - $total), 0));
		}

		if (($elementId == 502 || $elementId == 503) && isset($Missiles) && isset($MaxMissiles))
		{
			$ActuMissiles 	= $Missiles[502] + (2 * $Missiles[503]);
			$MissilesSpace 	= $MaxMissiles - $ActuMissiles;

			if ($MissilesSpace > 0)
			{
				if ($elementId == 502)
					$count = min($count, $MissilesSpace);
				else
					$count = min($count, floor($MissilesSpace / 2));
			}
			else
				$count = 0;
		}

		if (!$count)
			return;

		$count = min($count, Building::GetMaxConstructibleElements($elementId, $this->planet, $this->user));

		if ($count > 0)
		{
			$Ressource = Building::GetElementRessources($elementId, $count, $this->user);

			$this->planet->metal 		-= $Ressource['metal'];
			$this->planet->crystal 		-= $Ressource['crystal'];
			$this->planet->deuterium 	-= $Ressource['deuterium'];

			if (!isset($this->queue[self::QUEUE_TYPE_SHIPYARD]))
				$this->queue[self::QUEUE_TYPE_SHIPYARD] = [];

			$this->queue[self::QUEUE_TYPE_SHIPYARD][] = [
				'i' => $elementId,
				'l' => $count,
				't' => 0,
				's' => 0,
				'e' => 0,
				'd' => 0
			];

			$this->planet->update(
			[
				'metal' 	=> $this->planet->metal,
				'crystal' 	=> $this->planet->crystal,
				'deuterium' => $this->planet->deuterium,
				'queue' 	=> json_encode($this->queue)
			]);
		}
	}

	private function saveQueue ()
	{
		$this->checkQueue();
		$this->planet->queue = json_encode($this->get());
		$this->planet->update();
	}

	private function checkQueue ()
	{
		$types = $this->getTypes();
		
		foreach ($this->queue AS $key => $value)
		{
			if (!in_array((string) $key, $types))
			{
				unset($this->queue[$key]);
			}
			elseif (!count($value))
				unset($this->queue[$key]);
		}
	}
}