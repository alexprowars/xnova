<?
namespace App\Models;

use Phalcon\Mvc\Model;

/**
 * Class Planet
 * @package App\Models
 * @property \App\Database db
 */
class Planet extends Model
{
	private $db;

	public function onConstruct()
	{
		$this->db = $this->getDi()->getShared('db');
	}

	public function assignUser (User $user)
	{

	}

	public function checkOwnerPlanet ()
	{
		if ($this->data['id_owner'] != $this->user->data['id'] && $this->data['id_ally'] > 0 && ($this->data['id_ally'] != $this->user->data['ally_id'] || !$this->user->data['ally']['rights']['planet']))
		{
			sql::build()->update('game_users')->setField('current_planet', $this->user->data['id_planet'])->where('id', '=', $this->user->data['id'])->execute();

			$this->data['current_planet'] = $this->user->data['id_planet'];

			$this->load_from_id($this->user->data['id_planet']);

			return false;
		}

		return true;
	}

	public function CheckPlanetUsedFields ()
	{
		global $resource, $reslist;

		$cnt = 0;

		foreach ($reslist['allowed'][$this->data['planet_type']] AS $type)
			$cnt += $this->data[$resource[$type]];

		if ($this->data['field_current'] != $cnt)
		{
			$this->data['field_current'] = $cnt;

			$this->saveData(Array('field_current' => $this->data['field_current']));
		}
	}

	public function createByUserId ($user_id)
	{
		$Galaxy = $this->config->app->LastSettedGalaxyPos;
		$System = $this->config->app->LastSettedSystemPos;
		$Planet = $this->config->app->LastSettedPlanetPos;

		do
		{
			$free = self::getFreePositions($Galaxy, $System, round($this->config->game->maxPlanetInSystem * 0.2), round($this->config->game->maxPlanetInSystem * 0.8));

			if (count($free) > 0)
				$position = $free[array_rand($free)];
			else
				$position = 0;

            if ($position > 0 && $Planet < core::getConfig('maxRegPlanetsInSystem', 3))
				$Planet += 1;
            else
			{
				$Planet = 1;

				if ($System >= $this->config->game->maxSystemInGalaxy)
				{
					$System = 1;

					if ($Galaxy >= $this->config->game->maxGalaxyInWorld)
						$Galaxy = 1;
					else
						$Galaxy += 1;
				}
				else
					$System += 1;
            }
		}
		while ($this->isPositionFree($Galaxy, $System, $position) === false);

		if (system::CreateOnePlanetRecord($Galaxy, $System, $position, $user_id, _getText('sys_plnt_defaultname'), true) !== false)
		{
			core::updateConfig('LastSettedGalaxyPos', $Galaxy);
			core::updateConfig('LastSettedSystemPos', $System);
			core::updateConfig('LastSettedPlanetPos', $Planet);

			core::clearConfig();

			$PlanetID = db::first(db::query("SELECT `id` FROM game_planets WHERE `id_owner` = '" . $user_id . "' LIMIT 1;", true));

			sql::build()->update('game_users')->set(Array
			(
				'id_planet'		 => $PlanetID,
				'current_planet' => $PlanetID,
				'galaxy'		 => $Galaxy,
				'system'		 => $System,
				'planet'		 => $position
			))
			->where('id', '=', $user_id)->execute();

			return $PlanetID;
		}
		else
			return false;
	}

	public function createPlanet ($Galaxy, $System, $Position, $PlanetOwnerID, $PlanetName = '', $HomeWorld = false, $Base = false)
	{
		if (self::isPositionFree($Galaxy, $System, $Position))
		{
			$planet = $this->sizeRandomiser($Position, $HomeWorld, $Base);

			$planet['metal'] 		= BUILD_METAL;
			$planet['crystal'] 		= BUILD_CRISTAL;
			$planet['deuterium'] 	= BUILD_DEUTERIUM;

			$planet['galaxy'] = $Galaxy;
			$planet['system'] = $System;
			$planet['planet'] = $Position;

			$planet['planet_type'] = 1;

			if ($Base)
				$planet['planet_type'] = 5;

			$planet['id_owner'] = $PlanetOwnerID;
			$planet['last_update'] = time();
			$planet['name'] = ($PlanetName == '') ? _getText('sys_colo_defaultname') : $PlanetName;

			sql::build()->insert('game_planets')->set($planet)->execute();

			$planetId = db::insert_id();

			if (isset($_SESSION['fleet_shortcut']))
				unset($_SESSION['fleet_shortcut']);

			return $planetId;
		}
		else
			return false;
	}

	public function sizeRandomiser ($Position, $HomeWorld = false, $Base = false)
	{
		$planetData = array();
		require(APP_PATH.'app/varsPlanet.php');

		$return = array();

		if ($HomeWorld)
			$return['field_max'] = $this->config->game->get('initial_fields', 163);
		elseif ($Base)
			$return['field_max'] = $this->config->game->get('initial_base_fields', 10);
		else
			$return['field_max'] = (int) floor($planetData[$Position]['fields'] * $this->config->game->get('planetFactor', 1));

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

	public function getFreePositions ($galaxy, $system, $start = 1, $end = false)
	{
		if ($end === false)
			$end = $this->config->game->maxPlanetInSystem;

		$search = $this->db->extractResult($this->db->query("SELECT id, planet FROM game_planets WHERE galaxy = '".$galaxy."' AND system = '".$system."' AND planet >= '".$start."' AND planet <= '".$end."'"), 'planet');

		$result = array();

		for ($i = $start; $i <= $end; $i++)
		{
			if (!isset($search[$i]))
				$result[] = $i;
		}

		return $result;
	}
}

?>