<?php

namespace App;

use App\Models\Planet;
use App\Models\User;

class Queue
{
	private $queue = array();

	const QUEUE_TYPE_BUILDING = 'building';
	const QUEUE_TYPE_RESEARCH = 'research';
	const QUEUE_TYPE_SHIPYARD = 'shipyard';
	/**
	 * @var user
	 */
	private $user;
	/**
	 * @var \Xnova\planet
	 */
	private $planet;

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
			$this->queue = array();
	}
	
	public function setUserObject (User $user)
	{
		$this->user = $user;
	}
	
	public function setPlanetObject (Planet $planet)
	{
		$this->planet = $planet;
	}

	public function add($elementId, $count = 1, $destroy = false)
	{
		if (in_array($elementId, $reslist['build']))
			$this->addBuildingToQueue($elementId, $destroy);
		elseif (in_array($elementId, $reslist['tech']) || in_array($elementId, $reslist['tech_f']))
			$this->addTechToQueue($elementId);
		elseif (in_array($elementId, $reslist['fleet']) || in_array($elementId, $reslist['defense']))
			$this->addShipyardToQueue($elementId, $count);
	}

	public function delete($elementId, $listId = 0)
	{
		if (in_array($elementId, $reslist['build']))
			$this->deleteBuildingInQueue($listId);
		elseif (in_array($elementId, $reslist['tech']) || in_array($elementId, $reslist['tech_f']))
			$this->deleteTechInQueue($elementId);
	}

	public function get($queueType = '')
	{
		if (!$queueType)
			return $this->queue;
		elseif (isset($this->queue[$queueType]))
			return $this->queue[$queueType];
		else
			return array();
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
		$maxBuidSize = MAX_BUILDING_QUEUE_SIZE;
		if ($this->user->rpg_constructeur > time())
			$maxBuidSize += 2;

		$actualCount = $this->getCount(self::QUEUE_TYPE_BUILDING);

		if ($actualCount < $maxBuidSize)
			$queueID = $actualCount + 1;
		else
			$queueID = false;

		$currentMaxFields = CalculateMaxPlanetFields($this->planet->data);

		if ($this->planet->data["field_current"] < ($currentMaxFields - $actualCount) || $destroy)
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

			$ActualLevel = $this->planet->data[$resource[$elementId]];

			if (!$destroy)
			{
				$BuildLevel = $ActualLevel + 1 + $inArray;
				$this->planet->{$resource[$elementId]} += $inArray;
				$BuildTime = GetBuildingTime($this->user, $this->planet->data, $elementId);
				$this->planet->{$resource[$elementId]} -= $inArray;
			}
			else
			{
				$BuildLevel = $ActualLevel - 1 + $inArray;
				$this->planet->{$resource[$elementId]} -= $inArray;
				$BuildTime = GetBuildingTime($this->user, $this->planet->data, $elementId) / 2;
				$this->planet->{$resource[$elementId]} += $inArray;
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
				$this->queue[self::QUEUE_TYPE_BUILDING] = array();

			$this->queue[self::QUEUE_TYPE_BUILDING][] = array
			(
				'i' => $elementId,
				'l' => $BuildLevel,
				't' => 0,
				's' => 0,
				'e' => $BuildEndTime,
				'd' => $destroy ? 1 : 0
			);

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
					$listArray['t'] = GetBuildingTime($this->user, $this->planet->data, $listArray['i']);

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
				
			$this->planet->data['queue'] = json_encode($newQueue);

			sql::build()->update('game_planets')->setField('queue', $this->planet->data['queue']);
			
			if ($canceledArray['s'] > 0)
			{
				$cost = GetBuildingPrice($this->user, $this->planet->data, $canceledArray['i'], true, ($canceledArray['d'] == 1));

				$this->planet->data['metal'] 		+= $cost['metal'];
				$this->planet->data['crystal'] 		+= $cost['crystal'];
				$this->planet->data['deuterium'] 	+= $cost['deuterium'];

				sql::build()->set(array
				(
					'metal' 	=> $this->planet->data['metal'],
					'crystal' 	=> $this->planet->data['crystal'],
					'deuterium' => $this->planet->data['deuterium']
				));
			}

			sql::build()->where('id', '=', $this->planet->data['id'])->execute();
		}
	}

	private function addTechToQueue ($elementId)
	{
		if (!is_array($TechHandle))
			$TechHandle = $this->planet->HandleTechnologieBuild($this->planet, $this->user);

		$spaceLabs = array();

		if ($this->user->data[$resource[123]] > 0)
		{
			$spaceLabs = $this->planet->getNetworkLevel();
		}

		if (is_array($TechHandle['WorkOn']))
			$WorkingPlanet = $TechHandle['WorkOn'];
		else
			$WorkingPlanet = $this->planet->data;

		$WorkingPlanet['spaceLabs'] = $spaceLabs;

		if (IsTechnologieAccessible($this->user->data, $WorkingPlanet, $elementId) && IsElementBuyable($this->user, $WorkingPlanet, $elementId) && $WorkingPlanet['b_tech_id'] == 0 && !(isset($pricelist[$elementId]['max']) && $this->user->data[$resource[$elementId]] >= $pricelist[$elementId]['max']))
		{
			$costs = GetBuildingPrice($this->user, $WorkingPlanet, $elementId);

			$WorkingPlanet['metal'] 	-= $costs['metal'];
			$WorkingPlanet['crystal'] 	-= $costs['crystal'];
			$WorkingPlanet['deuterium'] -= $costs['deuterium'];

			$time = GetBuildingTime($this->user, $WorkingPlanet, $elementId);

			$this->queue[self::QUEUE_TYPE_RESEARCH] = array();

			$this->queue[self::QUEUE_TYPE_RESEARCH][] = array
			(
				'i' => $elementId,
				'l' => ($this->user->data[$resource[$elementId]] + 1),
				't' => $time,
				's' => time(),
				'e' => time() + $time,
				'd' => 0
			);
			
			$WorkingPlanet['queue'] = json_encode($this->queue);

			$this->user->data["b_tech_planet"] = $WorkingPlanet["id"];
			$this->saveTechToQueue($WorkingPlanet);
		}
	}

	private function deleteTechInQueue ($elementId, $listId = 0)
	{
		if (!is_array($TechHandle))
			$TechHandle = $this->planet->HandleTechnologieBuild($this->planet, $this->user);

		if (isset($this->queue[self::QUEUE_TYPE_RESEARCH][$listId]) && $TechHandle['OnWork'] && $this->queue[self::QUEUE_TYPE_RESEARCH][$listId]['i'] == $elementId)
		{
			if (is_array($TechHandle['WorkOn']))
				$WorkingPlanet = $TechHandle['WorkOn'];
			else
				$WorkingPlanet = $this->planet->data;

			$nedeed = GetBuildingPrice($this->user, $WorkingPlanet, $elementId);

			if ($TechHandle['WorkOn']['id'] == $this->planet->data['id'])
			{
				$this->planet->data['metal'] += $nedeed['metal'];
				$this->planet->data['crystal'] += $nedeed['crystal'];
				$this->planet->data['deuterium'] += $nedeed['deuterium'];
			}

			$WorkingPlanet['metal'] 	+= $nedeed['metal'];
			$WorkingPlanet['crystal'] 	+= $nedeed['crystal'];
			$WorkingPlanet['deuterium'] += $nedeed['deuterium'];
			
			unset($this->queue[self::QUEUE_TYPE_RESEARCH][$listId]);

			if (!count($this->queue[self::QUEUE_TYPE_BUILDING]))
				unset($this->queue[self::QUEUE_TYPE_BUILDING]);

			$WorkingPlanet['queue'] = json_encode($this->queue);
			
			$this->user->data['b_tech_planet'] = 0;
			$this->saveTechToQueue($WorkingPlanet);
		}
	}

	private function saveTechToQueue ($WorkingPlanet)
	{
		sql::build()->update('game_planets')->set(Array
		(
			'queue'		=> $WorkingPlanet['queue'],
			'metal'		=> $WorkingPlanet['metal'],
			'crystal'	=> $WorkingPlanet['crystal'],
			'deuterium'	=> $WorkingPlanet['deuterium']
		))
		->where('id', '=', $WorkingPlanet['id'])->execute();

		sql::build()->update('game_users')->setField('b_tech_planet', $this->user->data['b_tech_planet'])->where('id', '=', $this->user->data['id'])->execute();
	}

	private function addShipyardToQueue ($elementId, $count)
	{
		if (!IsTechnologieAccessible($this->user->data, $this->planet->data, $elementId))
			return;

		$BuildArray = $this->get(self::QUEUE_TYPE_SHIPYARD);

		if ($elementId == 502 || $elementId == 503)
		{
			$Missiles[502] = $this->planet->data[$resource[502]];
			$Missiles[503] = $this->planet->data[$resource[503]];

			$MaxMissiles = $this->planet->data[$resource[44]] * 10;

			foreach ($BuildArray AS $item)
			{
				if (($item['i'] == 502 || $item['i'] == 503) && $item['l'] != 0)
					$Missiles[$item['i']] += $item['l'];
			}
		}

		if (isset($pricelist[$elementId]['max']))
		{
			$total = $this->planet->data[$resource[$elementId]];

			if (isset($BuildArray[$elementId]))
				$total += $BuildArray[$elementId];

			$count = min($count, max(($pricelist[$elementId]['max'] - $total), 0));
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

		$count = min($count, GetMaxConstructibleElements($elementId, $this->planet->data, $this->user));

		if ($count > 0)
		{
			$Ressource = GetElementRessources($elementId, $count, $this->user);

			$this->planet->data['metal'] 		-= $Ressource['metal'];
			$this->planet->data['crystal'] 		-= $Ressource['crystal'];
			$this->planet->data['deuterium'] 	-= $Ressource['deuterium'];

			if (!isset($this->queue[self::QUEUE_TYPE_SHIPYARD]))
				$this->queue[self::QUEUE_TYPE_SHIPYARD] = array();

			$this->queue[self::QUEUE_TYPE_SHIPYARD][] = array
			(
				'i' => $elementId,
				'l' => $count,
				't' => 0,
				's' => 0,
				'e' => 0,
				'd' => 0
			);

			sql::build()->update('game_planets')->set(Array
			(
				'metal' 	=> $this->planet->data['metal'],
				'crystal' 	=> $this->planet->data['crystal'],
				'deuterium' => $this->planet->data['deuterium'],
				'queue' 	=> json_encode($this->queue)
			))
			->where('id', '=', $this->planet->data['id'])->execute();
		}
	}

	private function saveQueue ()
	{
		$this->checkQueue();
		$this->planet->data['queue'] = json_encode($this->get());
		
		sql::build()->update('game_planets')->set(Array
		(
			'queue' => $this->planet->data['queue']
		))
		->where('id', '=', $this->planet->data['id'])->execute();
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

?>