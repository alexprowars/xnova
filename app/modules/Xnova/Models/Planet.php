<?

namespace Xnova\Models;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Phalcon\Di;
use Xnova\Database;
use Xnova\Models\Planet\Build;
use Xnova\Models\Planet\Unit;
use Xnova\Queue;
use Phalcon\Mvc\Model;
use Xnova\Vars;
use Xnova\Models\User as UserModel;

/** @noinspection PhpHierarchyChecksInspection */

/**
 * @method static Planet[]|Model\ResultsetInterface find(mixed $parameters = null)
 * @method static Planet findFirst(mixed $parameters = null)
 * @method Database getWriteConnection
 */
class Planet extends Model
{
	use Build;
	use Unit;

	/** @var \Xnova\Models\User */
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
	public $debris_metal;
	public $debris_crystal;
	public $galaxy;
	public $planet;
	public $system;
	public $diameter;
	public $parent_planet;
	public $last_jump_time;
	public $destruyed;
	public $id_level;

	public $merchand;

	public $metal_perhour = 0;
	public $crystal_perhour = 0;
	public $deuterium_perhour = 0;

	public $spaceLabs;

	public function onConstruct()
	{
		$this->useDynamicUpdate(true);
	}

	public function getSource()
	{
		return DB_PREFIX."planets";
	}

	public function afterUpdate ()
	{
		$this->setSnapshotData($this->toArray());

		$this->_afterUpdateBuildings();
		$this->_afterUpdateUnits();
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

	public function getUser ()
	{
		return $this->user;
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
		if ($BuildLevelFactor > 10)
			/** @noinspection PhpUnusedLocalVariableInspection */
			$BuildLevelFactor = 10;

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
			$type = Vars::getItemType($ProdID);

			if ($type == Vars::ITEM_TYPE_BUILING && $this->getBuildLevel($ProdID) <= 0)
				continue;
			elseif ($type == Vars::ITEM_TYPE_FLEET && $this->getUnitCount($ProdID) <= 0)
				continue;

			if (!isset($registry->ProdGrid[$ProdID]))
				continue;

			$BuildLevelFactor = $BuildLevel = 0;

			if ($type == Vars::ITEM_TYPE_BUILING)
			{
				$build = $this->getBuild($ProdID);

				$BuildLevel = $build['level'];
				$BuildLevelFactor = $build['power'];
			}
			elseif ($type == Vars::ITEM_TYPE_FLEET)
			{
				$unit = $this->getUnit($ProdID);

				$BuildLevel = $unit['amount'];
				$BuildLevelFactor = $unit['power'];
			}

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

		if (!$simulation)
			$this->update();

		return true;
	}

	public function getNetworkLevel()
	{
		$list = [$this->getBuildLevel('laboratory')];

		if ($this->user->getTechLevel('intergalactic') > 0)
		{
			$items = $this->getWriteConnection()->query('SELECT b.id, b.level FROM game_planets_buildings b 
				LEFT JOIN game_planets p ON p.id = b.planet_id
					WHERE 
				b.build_id = :build AND p.id_owner = :user AND b.planet_id != :planet AND b.level > 0 AND p.destruyed = 0 AND p.planet_type = 1 
					ORDER BY 
				b.level DESC 
					LIMIT :level',
				[
					'build' => 31,
					'user' => $this->user->id,
					'planet' => $this->id,
					'level' => $this->user->getTechLevel('intergalactic')
				], [
					'level' => \PDO::PARAM_INT
				]
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