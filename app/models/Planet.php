<?
namespace App\Models;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use App\Building;
use App\Helpers;
use App\Queue;
use Phalcon\Mvc\Model;

/**
 * Class Planet
 * @package App\Models
 * @property \App\Database db
 * @property \App\Game game
 * @property \App\Models\User user
 */
class Planet extends Model
{
	private $db;
	private $user;
	private $game;
	private $storage;

	public $id;
	public $image;
	public $name;
	public $id_owner;
	public $id_ally;
	public $planet_type;
	public $field_current;
	public $field_max;
	public $last_update;
	public $battery_max;
	public $planet_updated;
	public $metal;
	public $crystal;
	public $deuterium;
	public $energy_used;
	public $energy_max = 0;
	public $energy_ak;
	public $temp_min;
	public $temp_max;
	public $queue;
	public $last_active;
	public $production_level;
	public $b_hangar;
	public $debris_metal;
	public $debris_crystal;
	public $galaxy;
	public $planet;
	public $system;
	public $diameter;
	public $parent_planet;
	public $phalanx;
	public $sprungtor;
	public $last_jump_time;
	public $destruyed;
	public $id_level;

	public $metal_mine;
	public $crystal_mine;
	public $deuterium_mine;
	public $solar_plant;
	public $fusion_plant;
	public $robot_factory;
	public $nano_factory;
	public $hangar;
	public $metal_store;
	public $crystal_store;
	public $deuterium_store;
	public $laboratory;
	public $terraformer;
	public $ally_deposit;
	public $silo;
	public $deuterium_mine_porcent;
	public $mondbasis;
	public $merchand;

	public $small_ship_cargo;
	public $big_ship_cargo;
	public $light_hunter;
	public $heavy_hunter;
	public $crusher;
	public $battle_ship;
	public $colonizer;
	public $recycler;
	public $spy_sonde;
	public $bomber_ship;
	public $solar_satelit;
	public $destructor;
	public $dearth_star;
	public $battle_cruiser;
	public $fly_base;

	public $corvete;
	public $interceptor;
	public $dreadnought;
	public $corsair;

	public $misil_launcher;
	public $small_laser;
	public $big_laser;
	public $gauss_canyon;
	public $ionic_canyon;
	public $buster_canyon;
	public $small_protection_shield;
	public $big_protection_shield;
	public $interceptor_misil;
	public $interplanetary_misil;

	public $metal_mine_porcent;
	public $crystal_mine_porcent;
	public $solar_plant_porcent;
	public $fusion_plant_porcent;
	public $solar_satelit_porcent;
	public $darkmat_mine_porcent;

	public $metal_perhour = 0;
	public $crystal_perhour = 0;
	public $deuterium_perhour = 0;

	public $spaceLabs;

	public function onConstruct()
	{
		$this->useDynamicUpdate(true);

		$this->db = $this->getDI()->getShared('db');
		$this->game = $this->getDI()->getShared('game');
		$this->storage = $this->getDI()->getShared('storage');
	}

	public function getSource()
	{
		return DB_PREFIX."planets";
	}

	public function afterUpdate ()
	{
		$this->setSnapshotData($this->toArray());
	}

	/**
	 * @param $galaxy
	 * @param $system
	 * @param $planet
	 * @param int $type
	 * @return Planet
	 */
	static function findByCoords ($galaxy, $system, $planet, $type = 1)
	{
		return self::findFirst(['galaxy = ?0 AND system = ?1 AND planet = ?2 AND planet_type = ?3', 'bind' => [$galaxy, $system, $planet, $type]]);
	}

	public function assignUser (User $user)
	{
		$this->user = $user;
	}

	public function checkOwnerPlanet ()
	{
		if ($this->id_owner != $this->user->id && $this->id_ally > 0 && ($this->id_ally != $this->user->ally_id || !$this->user->ally['rights']['planet']))
		{
			$this->user->planet_current = $this->user->planet_id;
			$this->user->update();

			$data = $this->findFirst($this->user->planet->id)->toArray();

			$this->assign($data);
			$this->setSnapshotData($data);

			return false;
		}

		return true;
	}

	public function checkUsedFields ()
	{
		$cnt = 0;

		foreach ($this->storage->reslist['allowed'][$this->planet_type] AS $type)
			$cnt += $this->{$this->storage->resource[$type]};

		if ($this->field_current != $cnt)
		{
			$this->field_current = $cnt;
			$this->update();
		}
	}

	public function isEmptyQueue ()
	{
		return (count(json_decode($this->queue, true)) == 0);
	}

	public function getProductionLevel ($Element, /** @noinspection PhpUnusedParameterInspection */$BuildLevel, /** @noinspection PhpUnusedParameterInspection */$BuildLevelFactor = 10)
	{
		$return = ['energy' => 0];

		$config = $this->getDI()->getShared('config');

		foreach ($this->storage->reslist['res'] AS $res)
			$return[$res] = 0;

		if (isset($this->storage->ProdGrid[$Element]))
		{
			/** @noinspection PhpUnusedLocalVariableInspection */
			$energyTech 	= $this->user->energy_tech;
			/** @noinspection PhpUnusedLocalVariableInspection */
			$BuildTemp		= $this->temp_max;

			foreach ($this->storage->reslist['res'] AS $res)
				$return[$res] = floor(eval($this->storage->ProdGrid[$Element][$res]) * $config->game->get('resource_multiplier') * $this->user->bonusValue($res));

			$energy = floor(eval($this->storage->ProdGrid[$Element]['energy']));

			if ($Element < 4)
				$return['energy'] = $energy;
			elseif ($Element == 4 || $Element == 12)
				$return['energy'] = floor($energy * $this->user->bonusValue('energy'));
			elseif ($Element == 212)
				$return['energy'] = floor($energy * $this->user->bonusValue('solar'));
		}

		return $return;
	}

	public function getProductions ()
	{
		$config = $this->getDI()->getShared('config');

		$Caps = [];

		foreach ($this->storage->reslist['res'] AS $res)
			$Caps[$res.'_perhour'] = 0;

		$Caps['energy_used'] 	= 0;
		$Caps['energy_max'] 	= 0;

		if ($this->user->isVacation())
			return;

		foreach ($this->storage->reslist['prod'] AS $ProdID)
		{
			$BuildLevelFactor = $this->{$this->storage->resource[$ProdID] . '_porcent'};
			$BuildLevel = $this->{$this->storage->resource[$ProdID]};

			if ($ProdID == 12 && $this->deuterium < 100)
				$BuildLevelFactor = 0;

			$result = $this->getProductionLevel($ProdID, $BuildLevel, $BuildLevelFactor);

			foreach ($this->storage->reslist['res'] AS $res)
				$Caps[$res.'_perhour'] += $result[$res];

			if ($ProdID < 4)
				$Caps['energy_used'] 	+= $result['energy'];
			else
				$Caps['energy_max'] 	+= $result['energy'];
		}

		if ($this->planet_type == 3 || $this->planet_type == 5)
		{
			foreach ($this->storage->reslist['res'] AS $res)
			{
				$config->game->offsetSet($res.'_basic_income', 0);
				$this->{$res.'_perhour'} = 0;
			}

			$this->energy_used 	= 0;
			$this->energy_max 	= 0;
		}
		else
		{
			foreach ($this->storage->reslist['res'] AS $res)
				$this->{$res.'_perhour'} = $Caps[$res.'_perhour'];

			$this->energy_used 	= $Caps['energy_used'];
			$this->energy_max 	= $Caps['energy_max'];
		}
	}

	public function resourceUpdate ($updateTime = 0, $simulation = false)
	{
		if (!$this->user instanceof User)
			return false;

		$config = $this->getDI()->getShared('config');

		if (!$updateTime)
			$updateTime = time();

		if ($updateTime < $this->last_update)
			return false;

		$this->planet_updated = true;

		foreach ($this->storage->reslist['res'] AS $res)
		{
			$this->{$res.'_max'}  = floor(($config->game->baseStorageSize + floor(50000 * round(pow(1.6, intval($this->{$res.'_store'}))))) * $this->user->bonusValue('storage'));
		}

		$this->battery_max = floor(250 * $this->{$this->storage->resource[4]});

		$this->getProductions();

		$productionTime = $updateTime - $this->last_update;
		$this->last_update = $updateTime;

		if ($productionTime < 0)
		{
			User::sendMessage(1, 0, time(), 1, '', print_r($this->toArray(), true).'||||||||'.$productionTime);
		}

		if (!defined('CRON'))
			$this->last_active = $this->last_update;

		if ($this->energy_max == 0)
		{
			foreach ($this->storage->reslist['res'] AS $res)
				$this->{$res.'_perhour'} = $config->game->get($res.'_basic_income');

			$production_level = 0;
		}
		elseif ($this->energy_max >= abs($this->energy_used))
		{
			$production_level = 100;
			$akk_add = round(($this->energy_max - abs($this->energy_used)) * ($productionTime / 3600), 2);

			if ($this->battery_max > ($this->energy_ak + $akk_add))
				$this->energy_ak += $akk_add;
			else
				$this->energy_ak = $this->battery_max;
		}
		else
		{
			if ($this->energy_ak > 0)
			{
				$need_en = ((abs($this->energy_used) - $this->energy_max) / 3600) * $productionTime;

				if ($this->energy_ak > $need_en)
				{
					$production_level = 100;
					$this->energy_ak -= round($need_en, 2);
				}
				else
				{
					$production_level = round((($this->energy_max + $this->energy_ak * 3600) / abs($this->energy_used)) * 100, 1);
					$this->energy_ak = 0;
				}
			}
			else
				$production_level = round(($this->energy_max / abs($this->energy_used)) * 100, 1);
		}

		$production_level = min(max($production_level, 0), 100);

		$this->production_level = $production_level;

		foreach ($this->storage->reslist['res'] AS $res)
		{
			$this->{$res.'_production'} = 0;

			if ($this->{$res} <= $this->{$res.'_max'})
			{
				$this->{$res.'_production'} = (($productionTime * ($this->{$res.'_perhour'} / 3600))) * (0.01 * $production_level);

				if (!$this->user->isVacation())
					$this->{$res.'_base'} = (($productionTime * ($config->game->get($res.'_basic_income', 0) / 3600)) * $config->game->get('resource_multiplier', 1));
				else
					$this->{$res.'_base'} = 0;

				$this->{$res.'_production'} = $this->{$res.'_production'} + $this->{$res.'_base'};

				if (($this->{$res} + $this->{$res.'_production'}) > $this->{$res.'_max'})
					$this->{$res.'_production'} = $this->{$res.'_max'} - $this->{$res};
			}

			$this->{$res.'_perhour'} = round(floatval($this->{$res.'_perhour'}) * (0.01 * $production_level));
			$this->{$res} += $this->{$res.'_production'};

			if ($this->{$res} < 0)
				$this->{$res} = 0;
		}

		$isBuilded = $this->HandleElementBuildingQueue($productionTime);

		if ($simulation && $isBuilded > 0)
			$simulation = false;

		if (!$simulation)
			$this->update();

		return true;
	}

	private function HandleElementBuildingQueue ($ProductionTime)
	{
		if (!$this->isEmptyQueue())
		{
			$queueManager = new Queue($this->queue);
			$queueArray = $queueManager->get();

			if ($queueManager->getCount($queueManager::QUEUE_TYPE_SHIPYARD))
			{
				$BuildQueue = $queueManager->get($queueManager::QUEUE_TYPE_SHIPYARD);

				$this->b_hangar = $BuildQueue[0]['s'];
				$this->b_hangar += $ProductionTime;

				$MissilesSpace = ($this->{$this->storage->resource[44]} * 10) - ($this->interceptor_misil + (2 * $this->interplanetary_misil));

				$max = [];

				foreach ($this->storage->pricelist as $id => $data)
				{
					if (isset($data['max']) && isset($this->{$this->storage->resource[$id]}))
						$max[$id] = $this->{$this->storage->resource[$id]};
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

					if (isset($this->storage->pricelist[$Item['i']]['max']))
					{
						if ($Item['l'] > $this->storage->pricelist[$Item['i']]['max'])
							$Item['l'] = $this->storage->pricelist[$Item['i']]['max'];

						if ($max[$Item['i']] + $Item['l'] > $this->storage->pricelist[$Item['i']]['max'])
							$Item['l'] = $this->storage->pricelist[$Item['i']]['max'] - $max[$Item['i']];

						if ($Item['l'] > 0)
							$max[$Item['i']] += $Item['l'];
						else
							$Item['l'] = 0;
					}

					$BuildArray[$Node] = [$Item['i'], $Item['l'], Building::GetBuildingTime($this->user, $this, $Item['i'])];
				}

				$UnFinished = false;

				$queueArray[$queueManager::QUEUE_TYPE_SHIPYARD] = [];

				foreach ($BuildArray as list($Element, $Count, $BuildTime))
				{
					if (!isset($this->storage->resource[$Element]))
						continue;

					while ($this->b_hangar >= $BuildTime && !$UnFinished)
					{
						$this->b_hangar -= $BuildTime;
						$Builded++;
						$this->{$this->storage->resource[$Element]}++;
						$Count--;

						if ($Count <= 0)
							break;
						elseif ($this->b_hangar < $BuildTime)
							$UnFinished = true;
					}

					if ($Count > 0)
					{
						$UnFinished = true;

						$queueArray[$queueManager::QUEUE_TYPE_SHIPYARD][] = ['i' => $Element, 'l' => $Count, 't' => 0, 's' => count($queueArray[$queueManager::QUEUE_TYPE_SHIPYARD]) == 0 ? $this->b_hangar : 0, 'e' => 0];
					}
				}

				if (!count($queueArray[$queueManager::QUEUE_TYPE_SHIPYARD]))
					unset($queueArray[$queueManager::QUEUE_TYPE_SHIPYARD]);

				$this->queue = json_encode($queueArray);

				return $Builded;
			}
		}

		return 0;
	}

	public function updateQueueList ()
	{
		$RetValue = false;

		if (!$this->isEmptyQueue())
		{
			$queueManager = new Queue($this->queue);

			$build_count = $queueManager->getCount($queueManager::QUEUE_TYPE_BUILDING);

			if ($build_count)
			{
				for ($i = 0; $i < $build_count; $i++)
				{
					if ($this->checkBuildingQueue($queueManager))
					{
						if (!$this->planet_updated)
							$this->resourceUpdate();

						$this->setNextBuildingQueue();
						$RetValue = true;
					}
					else
						break;
				}
			}

			if ($queueManager->getCount($queueManager::QUEUE_TYPE_RESEARCH) > 0 && $this->user->b_tech_planet == 0)
			{
				$this->user->b_tech_planet = $this->id;
				$this->user->update();
			}
		}

		if ($this->user->b_tech_planet)
			$RetValue = true;

		$this->checkResearchQueue();

		return $RetValue;
	}

	private function checkBuildingQueue (Queue $queueManager)
	{
		if ($queueManager->getCount($queueManager::QUEUE_TYPE_BUILDING))
		{
			$config = $this->getDI()->getShared('config');

			$QueueArray = $queueManager->get($queueManager::QUEUE_TYPE_BUILDING);

			$BuildArray = $QueueArray[0];
			$Element = $BuildArray['i'];

			array_shift($QueueArray);

			$ForDestroy = ($BuildArray['d'] == 1);

			if ($BuildArray['e'] <= time())
			{
				$Needed = Building::GetBuildingPrice($this->user, $this, $Element, true, $ForDestroy);
				$Units = $Needed['metal'] + $Needed['crystal'] + $Needed['deuterium'];

				// Мирный опыт за строения
				$XPBuildings = [1, 2, 3, 5, 22, 23, 24, 25];
				$XP = 0;

				if (in_array($Element, $XPBuildings))
				{
					if (!$ForDestroy)
						$XP += floor($Units / $config->game->get('buildings_exp_mult', 1000));
					else
						$XP -= floor($Units / $config->game->get('buildings_exp_mult', 1000));
				}

				if (!$ForDestroy)
				{
					$this->field_current++;
					$this->{$this->storage->resource[$Element]}++;
				}
				else
				{
					$this->field_current--;
					$this->{$this->storage->resource[$Element]}--;
				}

				$NewQueue = $queueManager->get();
				$NewQueue[$queueManager::QUEUE_TYPE_BUILDING] = $QueueArray;

				$queueManager->loadQueue($NewQueue);
				$queueManager->checkQueue();

				$this->queue = json_encode($queueManager->get());
				$this->update();

				if ($XP != 0 && $this->user->lvl_minier < $config->game->get('level.max_ind', 100))
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

	public function setNextBuildingQueue ()
	{
		$queueManager = new Queue($this->queue);

		if ($queueManager->getCount($queueManager::QUEUE_TYPE_BUILDING))
		{
			$QueueArray = $queueManager->get($queueManager::QUEUE_TYPE_BUILDING);

			if ($QueueArray[0]['s'] > 0)
				return;

			$config = $this->getDI()->getShared('config');

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
					$HaveRessources = Building::IsElementBuyable($this->user, $this, $ListIDArray['i'], true, $ForDestroy);

				if ($HaveRessources && Building::IsTechnologieAccessible($this->user, $this, $ListIDArray['i']))
				{
					$Needed = Building::GetBuildingPrice($this->user, $this, $ListIDArray['i'], true, $ForDestroy);

					$this->metal 		-= $Needed['metal'];
					$this->crystal 		-= $Needed['crystal'];
					$this->deuterium 	-= $Needed['deuterium'];

					$QueueArray[0]['s'] = time();

					$Loop = false;

					if ($config->log->get('buildings', false) == true)
					{
						$this->db->insertAsDict('game_log_history',
						[
							'user_id' 			=> $this->user->id,
							'time' 				=> time(),
							'operation' 		=> ($ForDestroy ? 2 : 1),
							'planet' 			=> $this->id,
							'from_metal' 		=> $this->metal + $Needed['metal'],
							'from_crystal' 		=> $this->crystal + $Needed['crystal'],
							'from_deuterium' 	=> $this->deuterium + $Needed['deuterium'],
							'to_metal' 			=> $this->metal,
							'to_crystal' 		=> $this->crystal,
							'to_deuterium' 		=> $this->deuterium,
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
						$Needed = Building::GetBuildingPrice($this->user, $this, $ListIDArray['i'], true, $ForDestroy);

						$Message = 'У вас недостаточно ресурсов чтобы начать строительство здания ' . _getText('tech', $ListIDArray['i']) . '.<br>Вам необходимо ещё: <br>';
						if ($Needed['metal'] > $this->metal)
							$Message .= Helpers::pretty_number($Needed['metal'] - $this->metal) . ' металла<br>';
						if ($Needed['crystal'] > $this->crystal)
							$Message .= Helpers::pretty_number($Needed['crystal'] - $this->crystal) . ' кристалла<br>';
						if ($Needed['deuterium'] > $this->deuterium)
							$Message .= Helpers::pretty_number($Needed['deuterium'] - $this->deuterium) . ' дейтерия<br>';
						if (isset($Needed['energy_max']) && isset($this->energy_max) && $Needed['energy_max'] > $this->energy_max)
							$Message .= Helpers::pretty_number($Needed['energy_max'] - $this->energy_max) . ' энергии<br>';
					}

					if (isset($Message))
						User::sendMessage($this->user->id, 0, 0, 99, _getText('sys_buildlist'), $Message);

					array_shift($QueueArray);

					if (count($QueueArray) == 0)
						$Loop = false;
				}
			}

			$queueManager->checkQueue();
			$newQueue = $queueManager->get();

			$BuildEndTime = time();

			foreach ($QueueArray as &$ListIDArray)
			{
				$ListIDArray['t'] = Building::GetBuildingTime($this->user, $this, $ListIDArray['i']);

				if ($ListIDArray['d'])
					$ListIDArray['t'] = ceil($ListIDArray['t'] / 2);

				$BuildEndTime += $ListIDArray['t'];
				$ListIDArray['e'] = $BuildEndTime;
			}

			unset($ListIDArray);

			$newQueue[$queueManager::QUEUE_TYPE_BUILDING] = $QueueArray;
			$newQueue = json_encode($newQueue);

			if ($this->queue != $newQueue)
			{
				$this->queue = $newQueue;

				$this->db->query("LOCK TABLES ".$this->getSource()." WRITE");

				$this->update();

				$this->db->query("UNLOCK TABLES");
			}
		}
	}
	
	public function checkResearchQueue ()
	{
		$Result['planet'] 	= false;
		$Result['working'] 	= false;

		if ($this->user->b_tech_planet != 0)
		{
			if ($this->user->b_tech_planet != $this->id)
				$ThePlanet = Planet::findFirst($this->user->b_tech_planet);
			else
				$ThePlanet = $this;

			$queueManager 	= new Queue($ThePlanet->queue);
			$queueArray 	= $queueManager->get($queueManager::QUEUE_TYPE_RESEARCH);

			if (count($queueArray))
			{
				if ($queueArray[0]['e'] <= time())
				{
					$this->user->b_tech_planet = 0;
					$this->user->{$this->storage->resource[$queueArray[0]['i']]}++;

					$queueManager->checkQueue();
					$newQueue = $queueManager->get();
					unset($newQueue[$queueManager::QUEUE_TYPE_RESEARCH]);

					$ThePlanet->queue = json_encode($newQueue);
					$ThePlanet->update();
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

	public function getNetworkLevel()
	{
		$list = [$this->{$this->storage->resource[31]}];

		if ($this->user->{$this->storage->resource[123]} > 0)
		{
			$result = $this->find([
				'columns'		=> $this->storage->resource[31],
				'conditions'	=> 'id_owner = ?0 AND id != ?1 AND '.$this->storage->resource[31].' > 0 AND destruyed = 0 AND planet_type = 1',
				'bind'			=> [$this->user->id, $this->id],
				'limit'			=> $this->user->{$this->storage->resource[123]},
				'order'			=> $this->storage->resource[31].' DESC'
			]);

			foreach ($result as $row)
			{
				$list[] = $row->{$this->storage->resource[31]};
			}
		}

		return $list;
	}

	function getMaxFields ()
	{
		$config = $this->getDI()->getShared('config');

		return $this->field_max + ($this->{$this->storage->resource[33]} * 5) + ($config->game->fieldsByMoonBase * $this->{$this->storage->resource[41]});
	}

	public function getNextJumpTime ()
	{
		if ($this->{$this->storage->resource[43]} > 0)
		{
			$waitTime = (60 * 60) * (1 / $this->{$this->storage->resource[43]});
			$nextJumpTime = $this->last_jump_time + $waitTime;

			if ($nextJumpTime >= time())
				return $nextJumpTime - time();
		}

		return 0;
	}

	public function saveData (array $fields, $planetId = 0)
	{
		$this->db->updateAsDict($this->getSource(), $fields, ['conditions' => 'id = ?', 'bind' => array(($planetId > 0 ? $planetId : $this->id))]);
	}
}