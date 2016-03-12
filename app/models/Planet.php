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
	public $energy_max;
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

	public function initialize()
	{
		$this->useDynamicUpdate(true);
	}

	public function onConstruct()
	{
		$this->db = $this->getDi()->getShared('db');
		$this->game = $this->getDi()->getShared('game');
	}

	public function getSource()
	{
		return DB_PREFIX."planets";
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
		return self::findFirst("galaxy = ".$galaxy." AND system = ".$system." AND planet = ".$planet." AND planet_type = ".$type."");
	}

	public function assignUser (User $user)
	{
		$this->user = $user;
		$this->copyTempParams();
	}

	public function copyTempParams ()
	{
		$metadata = $this->toArray();

		if (is_array($metadata))
		{
			foreach ($metadata AS $key => $value)
				$this->{'~'.$key} = $value;

			$this->energy_max = 0;
		}
	}

	public function checkOwnerPlanet ()
	{
		if ($this->id_owner != $this->user->id && $this->id_ally > 0 && ($this->id_ally != $this->user->ally_id || !$this->user->ally['rights']['planet']))
		{
			$this->user->saveData(['planet_current' => $this->user->planet_id]);
			$this->user->planet_current = $this->user->planet_id;

			$this->assign($this->findFirst($this->user->planet->id)->toArray());

			return false;
		}

		return true;
	}

	public function CheckPlanetUsedFields ()
	{
		$cnt = 0;

		foreach ($this->game->reslist['allowed'][$this->planet_type] AS $type)
			$cnt += $this->{$this->game->resource[$type]};

		if ($this->field_current != $cnt)
		{
			$this->field_current = $cnt;
			$this->save();
		}
	}

	public function createByUserId ($user_id)
	{
		$config = $this->getDi()->getShared('config');

		$Galaxy = $config->app->LastSettedGalaxyPos;
		$System = $config->app->LastSettedSystemPos;
		$Planet = $config->app->LastSettedPlanetPos;

		do
		{
			$free = self::getFreePositions($Galaxy, $System, round($config->game->maxPlanetInSystem * 0.2), round($config->game->maxPlanetInSystem * 0.8));

			if (count($free) > 0)
				$position = $free[array_rand($free)];
			else
				$position = 0;

            if ($position > 0 && $Planet < $config->game->get('maxRegPlanetsInSystem', 3))
				$Planet += 1;
            else
			{
				$Planet = 1;

				if ($System >= $config->game->maxSystemInGalaxy)
				{
					$System = 1;

					if ($Galaxy >= $config->game->maxGalaxyInWorld)
						$Galaxy = 1;
					else
						$Galaxy += 1;
				}
				else
					$System += 1;
            }
		}
		while ($this->isPositionFree($Galaxy, $System, $position) === false);

		if ($this->createPlanet($Galaxy, $System, $position, $user_id, _getText('sys_plnt_defaultname'), true) !== false)
		{
			$this->game->updateConfig('LastSettedGalaxyPos', $Galaxy);
			$this->game->updateConfig('LastSettedSystemPos', $System);
			$this->game->updateConfig('LastSettedPlanetPos', $Planet);

			$PlanetID = $this->db->fetchColumn("SELECT `id` FROM game_planets WHERE `id_owner` = '" . $user_id . "' LIMIT 1");

			if (is_null($this->user))
				$this->user = User::findFirst($user_id);

			$this->user->saveData([
				'planet_id'		 => $PlanetID,
				'planet_current' => $PlanetID,
				'galaxy'		 => $Galaxy,
				'system'		 => $System,
				'planet'		 => $position
			], $user_id);

			return $PlanetID;
		}
		else
			return false;
	}

	public function createPlanet ($Galaxy, $System, $Position, $PlanetOwnerID, $PlanetName = '', $HomeWorld = false, $Base = false)
	{
		if (self::isPositionFree($Galaxy, $System, $Position))
		{
			$config = $this->getDi()->getShared('config');

			$planet = $this->sizeRandomiser($Position, $HomeWorld, $Base);

			$planet['metal'] 		= $config->game->baseMetalProduction;
			$planet['crystal'] 		= $config->game->baseCristalProduction;
			$planet['deuterium'] 	= $config->game->baseDeuteriumProduction;

			$planet['galaxy'] = $Galaxy;
			$planet['system'] = $System;
			$planet['planet'] = $Position;

			$planet['planet_type'] = 1;

			if ($Base)
				$planet['planet_type'] = 5;

			$planet['id_owner'] = $PlanetOwnerID;
			$planet['last_update'] = time();
			$planet['name'] = ($PlanetName == '') ? _getText('sys_colo_defaultname') : $PlanetName;

			$this->db->insertAsDict('game_planets', $planet);

			$planetId = $this->db->lastInsertId();

			if (isset($_SESSION['fleet_shortcut']))
				unset($_SESSION['fleet_shortcut']);

			return $planetId;
		}
		else
			return false;
	}

	public function createMoon ($Galaxy, $System, $Planet, $Owner, $Chance)
	{
		$MoonPlanet = $this->db->query("SELECT * FROM game_planets WHERE `galaxy` = '" . $Galaxy . "' AND `system` = '" . $System . "' AND `planet` = '" . $Planet . "' AND planet_type = 1")->fetch();

		if ($MoonPlanet['parent_planet'] == 0 && $MoonPlanet['id'] != 0)
		{
			$maxtemp = $MoonPlanet['temp_max'] - rand(10, 45);
			$mintemp = $MoonPlanet['temp_min'] - rand(10, 45);

			$size = floor(pow(mt_rand(10, 20) + 3 * $Chance, 0.5) * 1000);

			$this->db->insertAsDict('game_planets',
			[
				'name' 			=> _getText('sys_moon'),
				'id_owner' 		=> $Owner,
				'galaxy' 		=> $Galaxy,
				'system' 		=> $System,
				'planet' 		=> $Planet,
				'planet_type' 	=> 3,
				'last_update' 	=> time(),
				'image' 		=> 'mond',
				'diameter' 		=> $size,
				'field_max' 	=> 1,
				'temp_min' 		=> $maxtemp,
				'temp_max' 		=> $mintemp,
				'metal' 		=> 0,
				'crystal' 		=> 0,
				'deuterium' 	=> 0
			]);

			$QryGetMoonId = $this->db->lastInsertId();

			$this->db->updateAsDict('game_planets', ['parent_planet' => $QryGetMoonId], 'id = '.$MoonPlanet['id']);

			return $QryGetMoonId;
		}
		else
			return false;
	}

	public function sizeRandomiser ($Position, $HomeWorld = false, $Base = false)
	{
		$config = $this->getDi()->getShared('config');

		$planetData = [];
		require(APP_PATH.'app/varsPlanet.php');

		$return = [];

		if ($HomeWorld)
			$return['field_max'] = $config->game->get('initial_fields', 163);
		elseif ($Base)
			$return['field_max'] = $config->game->get('initial_base_fields', 10);
		else
			$return['field_max'] = (int) floor($planetData[$Position]['fields'] * $config->game->get('planetFactor', 1));

		$return['diameter'] = (int) floor(1000 * sqrt($return['field_max']));

		$return['temp_max'] = $planetData[$Position]['temp'];
		$return['temp_min'] = $return['temp_max'] - 40;

		if ($Base)
		{
			$return['image'] = 'baseplanet01';
		}
		else
		{
			$imageNames = array_keys($planetData[$Position]['image']);
			$imageNameType = $imageNames[array_rand($imageNames)];

			$return['image']  = $imageNameType;
			$return['image'] .= 'planet';
			$return['image'] .= $planetData[$Position]['image'][$imageNameType] < 10 ? '0' : '';
			$return['image'] .= $planetData[$Position]['image'][$imageNameType];
		}

		return $return;
	}

	public function isPositionFree ($galaxy, $system, $position, $type = false)
	{
		if (!$galaxy || !$system || !$position)
			return false;

		$query = "SELECT `id` FROM game_planets WHERE ";

		if ($type !== false)
			$query .= "`planet_type` = '" . $type . "' AND ";

		$query .= "`galaxy` = '" . $galaxy . "' AND `system` = '" . $system . "' AND `planet` = '" . $position . "';";

		$exist = $this->db->query($query)->fetch();

		return (!isset($exist['id']));
	}

	public function getFreePositions ($galaxy, $system, $start = 1, $end = 15)
	{
		$search = $this->db->extractResult($this->db->query("SELECT id, planet FROM game_planets WHERE galaxy = '".$galaxy."' AND system = '".$system."' AND planet >= '".$start."' AND planet <= '".$end."'"), 'planet');

		$result = [];

		for ($i = $start; $i <= $end; $i++)
		{
			if (!isset($search[$i]))
				$result[] = $i;
		}

		return $result;
	}

	public function getProductionLevel ($Element, /** @noinspection PhpUnusedParameterInspection */$BuildLevel, /** @noinspection PhpUnusedParameterInspection */$BuildLevelFactor = 10)
	{
		$return = ['energy' => 0];

		$config = $this->getDi()->getShared('config');

		foreach ($this->game->reslist['res'] AS $res)
			$return[$res] = 0;

		if (isset($this->game->ProdGrid[$Element]))
		{
			/** @noinspection PhpUnusedLocalVariableInspection */
			$energyTech 	= $this->user->energy_tech;
			/** @noinspection PhpUnusedLocalVariableInspection */
			$BuildTemp		= $this->temp_max;

			foreach ($this->game->reslist['res'] AS $res)
				$return[$res] = floor(eval($this->game->ProdGrid[$Element][$res]) * $config->game->get('resource_multiplier') * $this->user->bonusValue($res));

			$energy = floor(eval($this->game->ProdGrid[$Element]['energy']));

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
		$config = $this->getDi()->getShared('config');

		$Caps = [];

		foreach ($this->game->reslist['res'] AS $res)
			$Caps[$res.'_perhour'] = 0;

		$Caps['energy_used'] 	= 0;
		$Caps['energy_max'] 	= 0;

		foreach ($this->game->reslist['prod'] AS $ProdID)
		{
			$BuildLevelFactor = $this->{$this->game->resource[$ProdID] . '_porcent'};
			$BuildLevel = $this->{$this->game->resource[$ProdID]};

			if ($ProdID == 12 && $this->deuterium < 100)
				$BuildLevelFactor = 0;

			$result = $this->getProductionLevel($ProdID, $BuildLevel, $BuildLevelFactor);

			foreach ($this->game->reslist['res'] AS $res)
				$Caps[$res.'_perhour'] += $result[$res];

			if ($ProdID < 4)
				$Caps['energy_used'] 	+= $result['energy'];
			else
				$Caps['energy_max'] 	+= $result['energy'];
		}

		if ($this->planet_type == 3 || $this->planet_type == 5)
		{
			foreach ($this->game->reslist['res'] AS $res)
			{
				$config->game->offsetSet($res.'_basic_income', 0);
				$this->{$res.'_perhour'} = 0;
			}

			$this->energy_used 	= 0;
			$this->energy_max 	= 0;
		}
		else
		{
			foreach ($this->game->reslist['res'] AS $res)
				$this->{$res.'_perhour'} = $Caps[$res.'_perhour'];

			$this->energy_used 	= $Caps['energy_used'];
			$this->energy_max 	= $Caps['energy_max'];
		}
	}

	public function PlanetResourceUpdate ($updateTime = 0, $simultion = false)
	{
		if (!$this->user instanceof User)
			return false;

		$config = $this->getDi()->getShared('config');

		if ($this->user->vacation != 0)
			$simultion = true;

		if (!$updateTime)
			$updateTime = time();

		if ($updateTime < $this->last_update)
			return false;

		$this->planet_updated = true;

		foreach ($this->game->reslist['res'] AS $res)
		{
			$this->{$res.'_max'}  = floor(($config->game->baseStorageSize + floor(50000 * round(pow(1.6, intval($this->{$res.'_store'}))))) * $this->user->bonusValue('storage'));
		}

		$this->battery_max = floor(250 * $this->{$this->game->resource[4]});

		$this->getProductions();

		$productionTime = $updateTime - $this->last_update;
		$this->last_update = $updateTime;

		if (!defined('CRON'))
			$this->last_active = $this->last_update;

		if ($this->energy_max == 0)
		{
			foreach ($this->game->reslist['res'] AS $res)
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

		foreach ($this->game->reslist['res'] AS $res)
		{
			$this->{$res.'_production'} = 0;

			if ($this->{$res} <= $this->{$res.'_max'})
			{
				$this->{$res.'_production'} = (($productionTime * ($this->{$res.'_perhour'} / 3600))) * (0.01 * $production_level);
				$this->{$res.'_base'} 		= (($productionTime * ($config->game->get($res.'_basic_income', 0) / 3600)) * $config->game->get('resource_multiplier', 1));

				$this->{$res.'_production'} = $this->{$res.'_production'} + $this->{$res.'_base'};

				if (($this->{$res} + $this->{$res.'_production'}) > $this->{$res.'_max'})
					$this->{$res.'_production'} = $this->{$res.'_max'} - $this->{$res};
			}

			$this->{$res.'_perhour'} = round(floatval($this->{$res.'_perhour'}) * (0.01 * $production_level));
			$this->{$res} += $this->{$res.'_production'};

			if ($this->{$res} < 0)
				$this->{$res} = 0;
		}

		if ($simultion)
		{
			$Builded = $this->HandleElementBuildingQueue($productionTime);

			$check = false;

			if (is_array($Builded))
			{
				foreach ($Builded AS $count)
				{
					if ($count > 0)
					{
						$check = true;
						break;
					}
				}
			}

			if ($check)
				$simultion = false;
		}

		if (!$simultion)
		{
			if (!isset($Builded))
				$Builded = $this->HandleElementBuildingQueue($productionTime);

			$arFields = [];

			if ($this->planet_type == 1)
			{
				foreach ($this->game->reslist['res'] AS $res)
				{
					if ($this->{$res} != $this->{'~'.$res})
						$arFields[$res] = $this->{$res};
				}

				if ($this->{'~energy_ak'} != $this->energy_ak)
					$arFields['energy_ak'] = $this->energy_ak;
			}

			if ($this->queue != $this->{'~queue'})
				$arFields['queue'] = $this->queue;

			if ($Builded != '')
			{
				foreach ($Builded as $Element => $Count)
					if ($Element <> '' && $this->{$this->game->resource[$Element]} != $this->{'~'.$this->game->resource[$Element]})
						$arFields[$this->game->resource[$Element]] = $this->{$this->game->resource[$Element]};
			}

			if (count($arFields) > 0 || ($this->last_update - $this->{'~last_update'}) >= 60)
			{
				$arFields['last_update'] = $this->last_update;

				if ($this->{'~last_active'} != $this->last_active)
					$arFields['last_active'] = $this->last_active;

				$this->db->updateAsDict('game_planets', $arFields, "id = ".$this->id." AND last_update != ".$this->last_update."");
			}
		}

		return true;
	}

	private function HandleElementBuildingQueue ($ProductionTime)
	{
		if ($this->queue != '[]')
		{
			$queueManager = new Queue($this->queue);
			$queueArray = $queueManager->get();

			if ($queueManager->getCount($queueManager::QUEUE_TYPE_SHIPYARD))
			{
				$BuildQueue = $queueManager->get($queueManager::QUEUE_TYPE_SHIPYARD);

				$this->b_hangar = $BuildQueue[0]['s'];
				$this->b_hangar += $ProductionTime;

				$MissilesSpace = ($this->{$this->game->resource[44]} * 10) - ($this->interceptor_misil + (2 * $this->interplanetary_misil));
				$Shield_1 = $this->small_protection_shield;
				$Shield_2 = $this->big_protection_shield;

				$BuildArray = [];
				$Builded = [];

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

					if ($Item['i'] == 407 || $Item['i'] == 408)
					{
						if ($Item['l'] > 1)
							$Item['l'] = 1;

						if ($Item['i'] == 407)
						{
							if ($Shield_1 == 1)
								$Item['l'] = 0;
							else
								$Shield_1 = 1;
						}
						else
						{
							if ($Shield_2 == 1)
								$Item['l'] = 0;
							else
								$Shield_2 = 1;
						}
					}

					$BuildArray[$Node] = [$Item['i'], $Item['l'], Building::GetBuildingTime($this->user, $this, $Item['i'])];
				}

				$UnFinished = false;

				$queueArray[$queueManager::QUEUE_TYPE_SHIPYARD] = [];

				foreach ($BuildArray as $Item)
				{
					if (!isset($this->game->resource[$Item[0]]))
						continue;

					$Element = $Item[0];
					$Count = $Item[1];
					$BuildTime = $Item[2];

					if (!isset($Builded[$Element]))
						$Builded[$Element] = 0;

					while ($this->b_hangar >= $BuildTime && !$UnFinished)
					{
						$this->b_hangar -= $BuildTime;
						$Builded[$Element]++;
						$this->{$this->game->resource[$Element]}++;
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
			else
				return '';
		}
		else
			return '';
	}

	public function UpdatePlanetBatimentQueueList ()
	{
		$RetValue = false;

		if ($this->queue != '[]')
		{
			$queueManager = new Queue($this->queue);

			$build_count = $queueManager->getCount($queueManager::QUEUE_TYPE_BUILDING);

			if ($build_count)
			{
				for ($i = 0; $i < $build_count; $i++)
				{
					if ($this->CheckPlanetBuildingQueue($queueManager))
					{
						if (!$this->planet_updated)
							$this->PlanetResourceUpdate();

						$this->SetNextQueueElementOnTop();
						$RetValue = true;
					}
					else
						break;
				}
			}

			if ($queueManager->getCount($queueManager::QUEUE_TYPE_RESEARCH) > 0 && $this->user->b_tech_planet == 0)
			{
				$this->db->updateAsDict('game_users', ['b_tech_planet' => $this->id], "id = ".$this->user->id);

				$this->user->b_tech_planet = $this->id;
			}
		}

		if ($this->checkTechnologieBuild())
			$RetValue = true;

		return $RetValue;
	}

	private function checkTechnologieBuild ()
	{
		if ($this->user->b_tech_planet != 0)
		{
			if ($this->user->b_tech_planet != $this->id)
				$WorkingPlanet = $this->db->query("SELECT id, queue FROM game_planets WHERE `id` = '" . $this->user->b_tech_planet . "';")->fetch();

			if (isset($WorkingPlanet))
				$ThePlanet = $WorkingPlanet;
			else
				$ThePlanet = $this->toArray();

			$queueManager = new Queue($ThePlanet['queue']);
			$queueArray = $queueManager->get($queueManager::QUEUE_TYPE_RESEARCH);

			if (count($queueArray))
			{
				if ($queueArray[0]['e'] <= time())
				{
					$this->user->{$this->game->resource[$queueArray[0]['i']]}++;

					$newQueue = $queueManager->get();
					unset($newQueue[$queueManager::QUEUE_TYPE_RESEARCH]);

					$this->db->updateAsDict('game_planets', ['queue' => json_encode($newQueue)], "id = ".$ThePlanet['id']);
					$this->db->updateAsDict(
						'game_users',
						[
							$this->game->resource[$queueArray[0]['i']]	=> $this->user->{$this->game->resource[$queueArray[0]['i']]},
							'b_tech_planet'	=> 0
						],
						"id = ".$this->user->id
					);

					$this->user->b_tech_planet = 0;

					if (!isset($WorkingPlanet))
						$this->queue = json_encode($newQueue);
				}
			}
			else
			{
				$this->db->updateAsDict('game_users', ['b_tech_planet' => 0], "id = ".$this->user->id);

				$this->user->b_tech_planet = 0;
			}
		}
		else
			return false;

		return true;
	}

	private function CheckPlanetBuildingQueue (Queue $queueManager)
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
					$this->{$this->game->resource[$Element]}++;
				}
				else
				{
					$this->field_current--;
					$this->{$this->game->resource[$Element]}--;
				}

				$NewQueue = $queueManager->get();
				$NewQueue[$queueManager::QUEUE_TYPE_BUILDING] = $QueueArray;

				$queueManager->loadQueue($NewQueue);

				$this->queue = json_encode($NewQueue);

				$this->db->updateAsDict('game_planets',
				[
					$this->game->resource[$Element]	=> $this->{$this->game->resource[$Element]},
					'queue'	=> $this->queue
				],
				"id = ".$this->id);

				if ($XP != 0 && $this->user->lvl_minier < $config->game->get('level.max_ind', 100))
				{
					$this->user->xpminier += $XP;

					if ($this->user->xpminier < 0)
						$this->user->xpminier = 0;

					$this->db->updateAsDict('game_users', ['xpminier' => $this->user->xpminier], "id = ".$this->user->id);
				}

				return true;
			}
			else
				return false;
		}

		return false;
	}

	public function SetNextQueueElementOnTop ()
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

				if ($ForDestroy && $this->{$this->game->resource[$ListIDArray['i']]} == 0)
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
							'level' 			=> ($this->{$this->game->resource[$ListIDArray['i']]} + 1)
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
						$this->game->sendMessage($this->user->id, 0, 0, 99, _getText('sys_buildlist'), $Message);

					array_shift($QueueArray);

					if (count($QueueArray) == 0)
						$Loop = false;
				}
			}

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
		}

		if (isset($newQueue) && $this->queue != $newQueue)
		{
			$this->queue = $newQueue;

			$this->db->query("LOCK TABLES ".$this->getSource()." WRITE");

			$this->saveData([
				'metal'		=> $this->metal,
				'crystal'	=> $this->crystal,
				'deuterium'	=> $this->deuterium,
				'queue'		=> $this->queue
			]);

			$this->db->query("UNLOCK TABLES");
		}
	}

	public function HandleTechnologieBuild ()
	{
		$Result['WorkOn'] = "";
		$Result['OnWork'] = false;

		if ($this->user->b_tech_planet != 0)
		{
			if ($this->user->b_tech_planet != $this->id)
				$WorkingPlanet = $this->db->query("SELECT * FROM game_planets WHERE `id` = '" . $this->user->b_tech_planet . "'")->fetch();

			if (isset($WorkingPlanet))
				$ThePlanet = $WorkingPlanet;
			else
				$ThePlanet = $this->toArray();

			$queueManager 	= new Queue($ThePlanet['queue']);
			$queueArray 	= $queueManager->get($queueManager::QUEUE_TYPE_RESEARCH);

			if (count($queueArray))
			{
				if ($queueArray[0]['e'] <= time())
				{
					$this->user->b_tech_planet = 0;
					$this->user->{$this->game->resource[$queueArray[0]['i']]}++;

					$newQueue = $queueManager->get();
					unset($newQueue[$queueManager::QUEUE_TYPE_RESEARCH]);

					$this->db->query("UPDATE game_planets SET `queue` = '".json_encode($newQueue)."' WHERE `id` = '" . $ThePlanet['id'] . "'");
					$this->db->query("UPDATE game_users SET `" . $this->game->resource[$queueArray[0]['i']] . "` = '" . $this->user->{$this->game->resource[$queueArray[0]['i']]} . "', `b_tech_planet` = '0' WHERE `id` = '" . $this->user->id . "'");

					if (!isset($WorkingPlanet))
						$this->assign($ThePlanet);
				}
				else
				{
					$Result['WorkOn'] = $ThePlanet;
					$Result['OnWork'] = true;
				}
			}
			else
			{
				$this->db->query("UPDATE game_users SET `b_tech_planet` = '0'  WHERE `id` = '" . $this->user->id . "'");

				$this->user->b_tech_planet = 0;
			}
		}

		return $Result;
	}

	public function getNetworkLevel()
	{
		$researchLevelList = [$this->{$this->game->resource[31]}];

		if ($this->user->{$this->game->resource[123]} > 0)
		{
			$researchResult = $this->db->query("SELECT ".$this->game->resource[31]." FROM game_planets WHERE id_owner='" . $this->user->id . "' AND id != '" . $this->id . "' AND ".$this->game->resource[31]." > 0 AND destruyed = 0 AND planet_type = 1 ORDER BY ".$this->game->resource[31]." DESC LIMIT ".$this->user->{$this->game->resource[123]}."");

			while ($researchRow = $researchResult->fetch())
			{
				$researchLevelList[] = $researchRow[$this->game->resource[31]];
			}
		}

		return $researchLevelList;
	}

	function getMaxFields ()
	{
		$config = $this->getDI()->getShared('config');

		return $this->field_max + ($this->{$this->game->resource[33]} * 5) + ($config->game->fieldsByMoonBase * $this->{$this->game->resource[41]});
	}

	public function GetNextJumpWaitTime (array $CurMoon = [])
	{
		if (count($CurMoon) == 0)
			$CurMoon = $this->toArray();

		$JumpGateLevel = $CurMoon[$this->game->resource[43]];
		$LastJumpTime = $CurMoon['last_jump_time'];

		if ($JumpGateLevel > 0)
		{
			$WaitBetweenJmp = (60 * 60) * (1 / $JumpGateLevel);
			$NextJumpTime = $LastJumpTime + $WaitBetweenJmp;

			if ($NextJumpTime >= time())
			{
				$RestWait = $NextJumpTime - time();
				$RestString = " " . Helpers::pretty_time($RestWait);
			}
			else
			{
				$RestWait = 0;
				$RestString = "";
			}
		}
		else
		{
			$RestWait = 0;
			$RestString = "";
		}

		$RetValue['string'] = $RestString;
		$RetValue['value'] = $RestWait;

		return $RetValue;
	}

	function checkAbandonMoonState (array &$lunarow)
	{
		if ($lunarow['luna_destruyed'] <= time())
		{
			$this->db->delete($this->getSource(), 'id = ?', [$lunarow['luna_id']]);
			$this->db->updateAsDict($this->getSource(), ['parent_planet' => 0], 'parent_planet = '.$lunarow['luna_id']);

			$lunarow['id_luna'] = 0;
		}
	}

	function checkAbandonPlanetState (array &$planet)
	{
		if ($planet['destruyed'] <= time())
		{
			$this->db->delete($this->getSource(), 'id = ?', [$planet['planet_id']]);

			if ($planet['parent_planet'] != 0)
				$this->db->delete($this->getSource(), 'id = ?', [$planet['parent_planet']]);
		}
	}

	public function saveData (array $fields, $planetId = 0)
	{
		$this->db->updateAsDict($this->getSource(), $fields, ['conditions' => 'id = ?', 'bind' => array(($planetId > 0 ? $planetId : $this->id))]);
	}
}