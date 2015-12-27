<?php

namespace App;

use App\Models\Planet;
use App\Models\User;
use Phalcon\Mvc\User\Component;

/**
 * Class ControllerBase
 * @property \Phalcon\Mvc\View view
 * @property \Phalcon\Tag tag
 * @property \Phalcon\Assets\Manager assets
 * @property \Phalcon\Db\Adapter\Pdo\Mysql db
 * @property \Phalcon\Mvc\Model\Manager modelsManager
 * @property \Phalcon\Session\Adapter\Memcache session
 * @property \Phalcon\Http\Response\Cookies cookies
 * @property \Phalcon\Http\Request request
 * @property \Phalcon\Http\Response response
 * @property \Phalcon\Mvc\Router router
 * @property \Phalcon\Cache\Backend\Memcache cache
 * @property \Phalcon\Mvc\Url url
 * @property \App\Models\User user
 * @property \App\Auth\Auth auth
 * @property \Phalcon\Mvc\Dispatcher dispatcher
 * @property \Phalcon\Flash\Direct flash
 * @property \Phalcon\Config|\stdClass config
 * @property \App\Game game
 */
class Queue
{
	private $queue = array();

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
		if (in_array($elementId, $this->game->reslist['build']))
			$this->addBuildingToQueue($elementId, $destroy);
		elseif (in_array($elementId, $this->game->reslist['tech']) || in_array($elementId, $this->game->reslist['tech_f']))
			$this->addTechToQueue($elementId);
		elseif (in_array($elementId, $this->game->reslist['fleet']) || in_array($elementId, $this->game->reslist['defense']))
			$this->addShipyardToQueue($elementId, $count);
	}

	public function delete($elementId, $listId = 0)
	{
		if (in_array($elementId, $this->game->reslist['build']))
			$this->deleteBuildingInQueue($listId);
		elseif (in_array($elementId, $this->game->reslist['tech']) || in_array($elementId, $this->game->reslist['tech_f']))
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

			$ActualLevel = $this->planet->{$this->game->resource[$elementId]};

			if (!$destroy)
			{
				$BuildLevel = $ActualLevel + 1 + $inArray;
				$this->planet->{$this->game->resource[$elementId]} += $inArray;
				$BuildTime = Building::GetBuildingTime($this->user, $this->planet, $elementId);
				$this->planet->{$this->game->resource[$elementId]} -= $inArray;
			}
			else
			{
				$BuildLevel = $ActualLevel - 1 + $inArray;
				$this->planet->{$this->game->resource[$elementId]} -= $inArray;
				$BuildTime = Building::GetBuildingTime($this->user, $this->planet, $elementId) / 2;
				$this->planet->{$this->game->resource[$elementId]} += $inArray;
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

			sql::build()->update('game_planets')->setField('queue', $this->planet->queue);
			
			if ($canceledArray['s'] > 0)
			{
				$cost = Building::GetBuildingPrice($this->user, $this->planet, $canceledArray['i'], true, ($canceledArray['d'] == 1));

				$this->planet->metal 		+= $cost['metal'];
				$this->planet->crystal 		+= $cost['crystal'];
				$this->planet->deuterium 	+= $cost['deuterium'];

				sql::build()->set(array
				(
					'metal' 	=> $this->planet->metal,
					'crystal' 	=> $this->planet->crystal,
					'deuterium' => $this->planet->deuterium
				));
			}

			sql::build()->where('id', '=', $this->planet->id)->execute();
		}
	}

	private function addTechToQueue ($elementId)
	{
		global $TechHandle;

		if (!is_array($TechHandle))
			$TechHandle = $this->planet->HandleTechnologieBuild($this->planet, $this->user);

		$spaceLabs = array();

		if ($this->user->{$this->game->resource[123]} > 0)
		{
			$spaceLabs = $this->planet->getNetworkLevel();
		}

		if (is_array($TechHandle['WorkOn']))
			$WorkingPlanet = $TechHandle['WorkOn'];
		else
			$WorkingPlanet = $this->planet->toArray();

		$WorkingPlanet['spaceLabs'] = $spaceLabs;

		if (Building::IsTechnologieAccessible($this->user, $WorkingPlanet, $elementId) && Building::IsElementBuyable($this->user, $WorkingPlanet, $elementId) && $WorkingPlanet['b_tech_id'] == 0 && !(isset($this->game->pricelist[$elementId]['max']) && $this->user->{$this->game->resource[$elementId]} >= $this->game->pricelist[$elementId]['max']))
		{
			$costs = Building::GetBuildingPrice($this->user, $WorkingPlanet, $elementId);

			$WorkingPlanet['metal'] 	-= $costs['metal'];
			$WorkingPlanet['crystal'] 	-= $costs['crystal'];
			$WorkingPlanet['deuterium'] -= $costs['deuterium'];

			$time = Building::GetBuildingTime($this->user, $WorkingPlanet, $elementId);

			$this->queue[self::QUEUE_TYPE_RESEARCH] = array();

			$this->queue[self::QUEUE_TYPE_RESEARCH][] = array
			(
				'i' => $elementId,
				'l' => ($this->user->{$this->game->resource[$elementId]} + 1),
				't' => $time,
				's' => time(),
				'e' => time() + $time,
				'd' => 0
			);
			
			$WorkingPlanet['queue'] = json_encode($this->queue);

			$this->user->b_tech_planet = $WorkingPlanet["id"];
			$this->saveTechToQueue($WorkingPlanet);
		}
	}

	private function deleteTechInQueue ($elementId, $listId = 0)
	{
		global $TechHandle;

		if (!is_array($TechHandle))
			$TechHandle = $this->planet->HandleTechnologieBuild($this->planet, $this->user);

		if (isset($this->queue[self::QUEUE_TYPE_RESEARCH][$listId]) && $TechHandle['OnWork'] && $this->queue[self::QUEUE_TYPE_RESEARCH][$listId]['i'] == $elementId)
		{
			if (is_array($TechHandle['WorkOn']))
				$WorkingPlanet = $TechHandle['WorkOn'];
			else
				$WorkingPlanet = $this->planet->toArray();

			$nedeed = Building::GetBuildingPrice($this->user, $WorkingPlanet, $elementId);

			if ($TechHandle['WorkOn']['id'] == $this->planet->id)
			{
				$this->planet->metal 		+= $nedeed['metal'];
				$this->planet->crystal 		+= $nedeed['crystal'];
				$this->planet->deuterium 	+= $nedeed['deuterium'];
			}

			$WorkingPlanet['metal'] 	+= $nedeed['metal'];
			$WorkingPlanet['crystal'] 	+= $nedeed['crystal'];
			$WorkingPlanet['deuterium'] += $nedeed['deuterium'];
			
			unset($this->queue[self::QUEUE_TYPE_RESEARCH][$listId]);

			if (!count($this->queue[self::QUEUE_TYPE_BUILDING]))
				unset($this->queue[self::QUEUE_TYPE_BUILDING]);

			$WorkingPlanet['queue'] = json_encode($this->queue);
			
			$this->user->b_tech_planet = 0;
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

		sql::build()->update('game_users')->setField('b_tech_planet', $this->user->b_tech_planet)->where('id', '=', $this->user->id)->execute();
	}

	private function addShipyardToQueue ($elementId, $count)
	{
		if (!Building::IsTechnologieAccessible($this->user, $this->planet, $elementId))
			return;

		$BuildArray = $this->get(self::QUEUE_TYPE_SHIPYARD);

		if ($elementId == 502 || $elementId == 503)
		{
			$Missiles[502] = $this->planet->{$this->game->resource[502]};
			$Missiles[503] = $this->planet->{$this->game->resource[503]};

			$MaxMissiles = $this->planet->{$this->game->resource[44]} * 10;

			foreach ($BuildArray AS $item)
			{
				if (($item['i'] == 502 || $item['i'] == 503) && $item['l'] != 0)
					$Missiles[$item['i']] += $item['l'];
			}
		}

		if (isset($pricelist[$elementId]['max']))
		{
			$total = $this->planet->{$this->game->resource[$elementId]};

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

		$count = min($count, Building::GetMaxConstructibleElements($elementId, $this->planet, $this->user));

		if ($count > 0)
		{
			$Ressource = Building::GetElementRessources($elementId, $count, $this->user);

			$this->planet->metal 		-= $Ressource['metal'];
			$this->planet->crystal 		-= $Ressource['crystal'];
			$this->planet->deuterium 	-= $Ressource['deuterium'];

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
				'metal' 	=> $this->planet->metal,
				'crystal' 	=> $this->planet->crystal,
				'deuterium' => $this->planet->deuterium,
				'queue' 	=> json_encode($this->queue)
			))
			->where('id', '=', $this->planet->id)->execute();
		}
	}

	private function saveQueue ()
	{
		$this->checkQueue();
		$this->planet->queue = json_encode($this->get());
		
		sql::build()->update('game_planets')->set(Array
		(
			'queue' => $this->planet->queue
		))
		->where('id', '=', $this->planet->id)->execute();
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