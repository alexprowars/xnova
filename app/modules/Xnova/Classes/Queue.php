<?php

namespace Xnova;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Models\Planet;
use Xnova\Models\User;
use Phalcon\Di;

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

	public function add($elementId, $count = 1, $destroy = false)
	{
		if (in_array($elementId, $this->registry->reslist['build']))
			$this->addBuildingToQueue($elementId, $destroy);
		elseif (in_array($elementId, $this->registry->reslist['tech']) || in_array($elementId, $this->registry->reslist['tech_f']))
			$this->addTechToQueue($elementId);
		elseif (in_array($elementId, $this->registry->reslist['fleet']) || in_array($elementId, $this->registry->reslist['defense']))
			$this->addShipyardToQueue($elementId, $count);
	}

	public function delete($elementId, $listId = 0)
	{
		if (in_array($elementId, $this->registry->reslist['build']))
			$this->deleteBuildingInQueue($listId);
		elseif (in_array($elementId, $this->registry->reslist['tech']) || in_array($elementId, $this->registry->reslist['tech_f']))
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

			$build = $this->planet->getBuild($elementId);

			if (!$build)
				return false;

			$ActualLevel = $build['level'];

			if (!$destroy)
			{
				$BuildLevel = $ActualLevel + 1 + $inArray;

				$this->planet->setBuild($elementId, $build['level'] + $inArray);
				$BuildTime = Building::GetBuildingTime($this->user, $this->planet, $elementId);
				$this->planet->setBuild($elementId, $build['level'] - $inArray);
			}
			else
			{
				$BuildLevel = $ActualLevel - 1 + $inArray;

				$this->planet->setBuild($elementId, $build['level'] - $inArray);
				$BuildTime = Building::GetBuildingTime($this->user, $this->planet, $elementId) / 2;
				$this->planet->setBuild($elementId, $build['level'] + $inArray);
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

	private function addTechToQueue ($elementId)
	{
		$TechHandle = $this->planet->checkResearchQueue();

		if (!$TechHandle['working'])
		{
			$spaceLabs = [];

			if ($this->user->{$this->registry->resource[123]} > 0)
				$spaceLabs = $this->planet->getNetworkLevel();

			$this->planet->spaceLabs = $spaceLabs;

			if (Building::IsTechnologieAccessible($this->user, $this->planet, $elementId) && Building::IsElementBuyable($this->user, $this->planet, $elementId) && !(isset($this->registry->pricelist[$elementId]['max']) && $this->user->{$this->registry->resource[$elementId]} >= $this->registry->pricelist[$elementId]['max']))
			{
				$costs = Building::GetBuildingPrice($this->user, $this->planet, $elementId);

				$this->planet->metal 		-= $costs['metal'];
				$this->planet->crystal 		-= $costs['crystal'];
				$this->planet->deuterium 	-= $costs['deuterium'];

				$time = Building::GetBuildingTime($this->user, $this->planet, $elementId);

				$this->queue[self::QUEUE_TYPE_RESEARCH] = [[
					'i' => $elementId,
					'l' => ($this->user->{$this->registry->resource[$elementId]} + 1),
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
			$Missiles[502] = $this->planet->{$this->registry->resource[502]};
			$Missiles[503] = $this->planet->{$this->registry->resource[503]};

			$MaxMissiles = $this->planet->{$this->registry->resource[44]} * 10;

			foreach ($BuildArray AS $item)
			{
				if (($item['i'] == 502 || $item['i'] == 503) && $item['l'] != 0)
					$Missiles[$item['i']] += $item['l'];
			}
		}

		if (isset($this->registry->pricelist[$elementId]['max']))
		{
			$total = $this->planet->{$this->registry->resource[$elementId]};

			if (isset($BuildArray[$elementId]))
				$total += $BuildArray[$elementId];

			$count = min($count, max(($this->registry->pricelist[$elementId]['max'] - $total), 0));
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
}