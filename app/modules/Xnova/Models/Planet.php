<?

namespace Xnova\Models;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Phalcon\Di;
use Phalcon\Exception;
use Xnova\Queue;
use Phalcon\Mvc\Model;
use Xnova\Vars;
use Xnova\Models\User as UserModel;

/** @noinspection PhpHierarchyChecksInspection */

/**
 * @method static Planet[]|Model\ResultsetInterface find(mixed $parameters = null)
 * @method static Planet findFirst(mixed $parameters = null)
 * @property \Xnova\Database db
 * @property \Xnova\Game game
 * @property \Xnova\Models\User user
 */
class Planet extends Model
{
	private $db;
	private $user;

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
	public $sprungtor;
	public $last_jump_time;
	public $destruyed;
	public $id_level;

	public $merchand;

	public $metal_perhour = 0;
	public $crystal_perhour = 0;
	public $deuterium_perhour = 0;

	public $spaceLabs;
	/**
	 * @var bool|array
	 */
	private $buildings = false;
	/**
	 * @var bool|array
	 */
	private $units = false;

	public function onConstruct()
	{
		$this->useDynamicUpdate(true);

		$this->db = $this->getDI()->getShared('db');
	}

	public function getSource()
	{
		return DB_PREFIX."planets";
	}

	public function afterUpdate ()
	{
		$this->setSnapshotData($this->toArray());

		if ($this->buildings !== false)
		{
			foreach ($this->buildings as &$building)
			{
				if ($building['id'] == 0 && $building['level'] > 0)
				{
					$this->db->insertAsDict(DB_PREFIX.'planets_buildings', [
						'planet_id' => $this->id,
						'build_id' => $building['type'],
						'level' => $building['level'],
						'power' => $building['power'] !== false ? $building['power'] : 10
					]);

					$building['id'] = $this->db->lastInsertId();
				}
				elseif ($building['level'] != $building['~level'] || $building['power'] != $building['~power'])
				{
					if ($building['level'] > 0)
					{
						$this->db->updateAsDict(DB_PREFIX.'planets_buildings', [
							'level' => $building['level'],
							'power' => $building['power']
						], ['conditions' => 'id = ?', 'bind' => [$building['id']]]);
					}
					else
						$this->db->delete(DB_PREFIX.'planets_buildings', 'id = ?', [$building['id']]);
				}

				$building['~level'] = $building['level'];
				$building['~power'] = $building['power'];
			}

			unset($building);
		}

		if ($this->units !== false)
		{
			foreach ($this->units as &$unit)
			{
				if ($unit['id'] == 0 && $unit['amount'] > 0)
				{
					$this->db->insertAsDict(DB_PREFIX.'planets_units', [
						'planet_id' => $this->id,
						'unit_id' => $unit['type'],
						'amount' => $unit['amount'],
					]);

					$unit['id'] = $this->db->lastInsertId();
				}
				elseif ($unit['amount'] != $unit['~amount'])
				{
					if ($unit['amount'] > 0)
					{
						$this->db->updateAsDict(DB_PREFIX.'planets_units', [
							'amount' => $unit['amount']
						], ['conditions' => 'id = ?', 'bind' => [$unit['id']]]);
					}
					else
						$this->db->delete(DB_PREFIX.'planets_units', 'id = ?', [$unit['id']]);
				}

				$unit['~amount'] = $unit['amount'];
			}

			unset($unit);
		}
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

	public function assignUser (UserModel $user)
	{
		$this->user = $user;
	}

	private function getBuildingsData ()
	{
		if ($this->buildings !== false)
			return;

		$this->buildings = [];

		$items = $this->db->query('SELECT * FROM '.DB_PREFIX.'planets_buildings WHERE planet_id = ?', [$this->id]);

		while ($item = $items->fetch())
		{
			$this->buildings[$item['build_id']] = [
				'id'		=> (int) $item['id'],
				'type'		=> (int) $item['build_id'],
				'level'		=> (int) $item['level'],
				'~level'	=> (int) $item['level'],
				'power'		=> (int) $item['power'],
				'~power'	=> (int) $item['power']
			];
		}
	}

	public function clearBuildingsData ()
	{
		$this->buildings = false;
	}

	public function getBuild ($buildId)
	{
		if (!is_numeric($buildId))
			$buildId = Vars::getIdByName($buildId);

		$buildId = (int) $buildId;

		if (!$buildId)
			throw new Exception('getBuild not found');

		if (!$buildId)
			return false;

		if ($this->buildings === false)
			$this->getBuildingsData();

		if (isset($this->buildings[$buildId]))
			return $this->buildings[$buildId];

		if (Vars::getItemType($buildId) != Vars::ITEM_TYPE_BUILING)
			return false;

		$this->buildings[$buildId] = [
			'id'		=> 0,
			'type'		=> $buildId,
			'level'		=> 0,
			'~level'	=> 0,
			'power'		=> false,
			'~power'	=> false
		];

		return $this->buildings[$buildId];
	}

	public function setBuild ($buildId, $level = false, $power = false)
	{
		$build = $this->getBuild($buildId);

		if ($level !== false)
			$this->buildings[$build['type']]['level'] = (int) $level;

		if ($power !== false)
		{
			$power = (int) $power;
			$power = min(10, max(0, $power));

			$this->buildings[$build['type']]['power'] = $power;
		}
	}

	public function getBuildLevel ($buildId)
	{
		$build = $this->getBuild($buildId);

		return $build ? $build['level'] : 0;
	}

	private function getUnitsData ()
	{
		if ($this->units !== false)
			return;

		$this->units = [];

		$items = $this->db->query('SELECT * FROM '.DB_PREFIX.'planets_units WHERE planet_id = ?', [$this->id]);

		while ($item = $items->fetch())
		{
			$this->units[$item['unit_id']] = [
				'id'		=> (int) $item['id'],
				'type'		=> (int) $item['unit_id'],
				'amount'	=> (int) $item['amount'],
				'~amount'	=> (int) $item['amount'],
			];
		}
	}

	public function clearUnitsData ()
	{
		$this->units = false;
	}

	public function getUnit ($unitId)
	{
		if (!is_numeric($unitId))
			$unitId = Vars::getIdByName($unitId);

		$unitId = (int) $unitId;

		if (!$unitId)
			throw new Exception('getUnit not found');

		if (!$unitId)
			return false;

		if ($this->units === false)
			$this->getUnitsData();

		if (isset($this->units[$unitId]))
			return $this->units[$unitId];

		if (!in_array(Vars::getItemType($unitId), [Vars::ITEM_TYPE_FLEET, Vars::ITEM_TYPE_DEFENSE]))
			return false;

		$this->units[$unitId] = [
			'id'		=> 0,
			'type'		=> $unitId,
			'amount'	=> 0,
			'~amount'	=> 0,
		];

		return $this->units[$unitId];
	}

	public function setUnit ($unitId, $count, $isDifferent = false)
	{
		$unit = $this->getUnit($unitId);

		if ($isDifferent)
			$this->units[$unit['type']]['amount'] = $unit['amount'] + (int) $count;
		else
			$this->units[$unit['type']]['amount'] = (int) $count;
	}

	public function getUnitCount ($unitId)
	{
		$unit = $this->getUnit($unitId);

		return $unit ? $unit['amount'] : 0;
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
		$this->getBuildingsData();

		$cnt = 0;

		foreach (Vars::getAllowedBuilds($this->planet_type) AS $type)
		{
			if (isset($this->buildings[$type]))
				$cnt += $this->buildings[$type]['level'];
		}

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

	public function getResourceProductionLevel ($Element, /** @noinspection PhpUnusedParameterInspection */$BuildLevel, /** @noinspection PhpUnusedParameterInspection */$BuildLevelFactor = 10)
	{
		$return = ['energy' => 0];

		$config = $this->getDI()->getShared('config');

		foreach (Vars::getResources() AS $res)
			$return[$res] = 0;

		$return['energy'] = 0;

		$production = Vars::getBuildProduction($Element);

		if (!$production)
			return $return;

		/** @noinspection PhpUnusedLocalVariableInspection */
		$energyTech 	= $this->user->getTechLevel('energy');
		/** @noinspection PhpUnusedLocalVariableInspection */
		$BuildTemp		= $this->temp_max;

		foreach (Vars::getResources() AS $res)
		{
			if (isset($production[$res]))
				$return[$res] = floor(eval($production[$res]) * $config->game->get('resource_multiplier') * $this->user->bonusValue($res));
		}

		if (isset($production['energy']))
		{
			$energy = floor(eval($production['energy']));

			if ($Element < 4)
				$return['energy'] = $energy;
			elseif ($Element == 4 || $Element == 12)
				$return['energy'] = floor($energy * $this->user->bonusValue('energy'));
			elseif ($Element == 212)
				$return['energy'] = floor($energy * $this->user->bonusValue('solar'));
		}

		return $return;
	}

	public function resourceProductions ()
	{
		$config = $this->getDI()->getShared('config');

		$this->energy_used 	= 0;
		$this->energy_max 	= 0;

		foreach (Vars::getResources() AS $res)
			$this->{$res.'_perhour'} = 0;

		if ($this->user->isVacation())
			return;

		if (in_array($this->planet_type, [3, 5]))
		{
			foreach (Vars::getResources() AS $res)
				$config->game->offsetSet($res.'_basic_income', 0);

			return;
		}

		$registry = Di::getDefault()->getShared('registry');

		foreach ($registry->reslist['prod'] AS $ProdID)
		{
			if (!isset($this->buildings[$ProdID]))
				continue;

			$BuildLevelFactor = $this->buildings[$ProdID]['power'];
			$BuildLevel = $this->buildings[$ProdID]['level'];

			if ($ProdID == 12 && $this->deuterium < 100)
				$BuildLevelFactor = 0;

			$result = $this->getResourceProductionLevel($ProdID, $BuildLevel, $BuildLevelFactor);

			foreach (Vars::getResources() AS $res)
				$this->{$res.'_perhour'} += $result[$res];

			if ($ProdID < 4)
				$this->energy_used += $result['energy'];
			else
				$this->energy_max += $result['energy'];
		}
	}

	public function resourceUpdate ($updateTime = 0, $simulation = false)
	{
		if (!$this->user instanceof UserModel)
			return false;

		$config = $this->getDI()->getShared('config');

		if (!$updateTime)
			$updateTime = time();

		if ($updateTime < $this->last_update)
			return false;

		$this->getBuildingsData();

		$this->planet_updated = true;

		foreach (Vars::getResources() AS $res)
			$this->{$res.'_max'}  = floor(($config->game->baseStorageSize + floor(50000 * round(pow(1.6, $this->getBuildLevel($res.'_store'))))) * $this->user->bonusValue('storage'));

		$this->battery_max = floor(250 * $this->getBuildLevel('solar_plant'));

		$this->resourceProductions();

		$productionTime = $updateTime - $this->last_update;
		$this->last_update = $updateTime;

		if (!defined('CRON'))
			$this->last_active = $this->last_update;

		if ($this->energy_max == 0)
		{
			foreach (Vars::getResources() AS $res)
				$this->{$res.'_perhour'} = $config->game->get($res.'_basic_income');

			$this->production_level = 0;
		}
		elseif ($this->energy_max >= abs($this->energy_used))
		{
			$this->production_level = 100;

			$energy = round(($this->energy_max - abs($this->energy_used)) * ($productionTime / 3600), 2);

			if ($this->battery_max > ($this->energy_ak + $energy))
				$this->energy_ak += $energy;
			else
				$this->energy_ak = $this->battery_max;
		}
		else
		{
			if ($this->energy_ak > 0)
			{
				$energy = ((abs($this->energy_used) - $this->energy_max) / 3600) * $productionTime;

				if ($this->energy_ak > $energy)
				{
					$this->production_level = 100;
					$this->energy_ak -= round($energy, 2);
				}
				else
				{
					$this->production_level = round((($this->energy_max + $this->energy_ak * 3600) / abs($this->energy_used)) * 100, 1);
					$this->energy_ak = 0;
				}
			}
			else
				$this->production_level = round(($this->energy_max / abs($this->energy_used)) * 100, 1);
		}

		$this->production_level = min(max($this->production_level, 0), 100);

		foreach (Vars::getResources() AS $res)
		{
			$this->{$res.'_production'} = 0;

			if ($this->{$res} <= $this->{$res.'_max'})
			{
				$this->{$res.'_production'} = ($productionTime * ($this->{$res.'_perhour'} / 3600)) * (0.01 * $this->production_level);

				if (!$this->user->isVacation())
					$this->{$res.'_base'} = ($productionTime * ($config->game->get($res.'_basic_income', 0) / 3600)) * $config->game->get('resource_multiplier', 1);
				else
					$this->{$res.'_base'} = 0;

				$this->{$res.'_production'} = $this->{$res.'_production'} + $this->{$res.'_base'};

				if (($this->{$res} + $this->{$res.'_production'}) > $this->{$res.'_max'})
					$this->{$res.'_production'} = $this->{$res.'_max'} - $this->{$res};
			}

			$this->{$res.'_perhour'} = round(floatval($this->{$res.'_perhour'}) * (0.01 * $this->production_level));
			$this->{$res} += $this->{$res.'_production'};

			if ($this->{$res} < 0)
				$this->{$res} = 0;
		}

		$isBuilded = $this->updateQueueList($productionTime);

		if ($simulation && $isBuilded > 0)
			$simulation = false;

		if (!$simulation)
			$this->update();

		return true;
	}

	public function updateQueueList ($time = 0)
	{
		$queueManager = new Queue($this->queue);
		$queueManager->setUserObject($this->user);
		$queueManager->setPlanetObject($this);

		return $queueManager->update($time);
	}

	public function getNetworkLevel()
	{
		$list = [$this->getBuildLevel('laboratory')];

		if ($this->user->getTechLevel('intergalactic') > 0)
		{
			$items = $this->db->query('SELECT id, level FROM game_planets_buildings 
					WHERE 
				build_id = ?0 AND id_owner = ?1 AND id != ?2 AND level > 0 AND destruyed = 0 AND planet_type = 1 
					ORDER BY 
				level DESC 
					LIMIT ?3',
				[31, $this->user->id, $this->id, $this->user->getTechLevel('intergalactic')]
			);

			while ($item = $items->fetch())
				$list[] = (int) $item['level'];
		}

		return $list;
	}

	public function getMaxFields ()
	{
		$config = $this->getDI()->getShared('config');

		$fields = $this->field_max;

		$fields += $this->getBuildLevel('terraformer') * 5;
		$fields += $config->game->fieldsByMoonBase * $this->getBuildLevel('moonbase');

		return $fields;
	}

	public function getNextJumpTime ()
	{
		$jumpGate = $this->getBuild('jumpgate');

		if ($jumpGate && $jumpGate['level'] > 0)
		{
			$waitTime = (60 * 60) * (1 / $jumpGate['level']);
			$nextJumpTime = $this->last_jump_time + $waitTime;

			if ($nextJumpTime >= time())
				return $nextJumpTime - time();
		}

		return 0;
	}
}