<?php

namespace Xnova;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Friday\Core\Options;
use Xnova\Models\Planet;
use Xnova\Models\User;
use Phalcon\Mvc\User\Component;

/**
 * Class Galaxy
 * @package App
 * @property \Phalcon\Mvc\View view
 * @property \Phalcon\Tag tag
 * @property \Phalcon\Assets\Manager assets
 * @property \Xnova\Database db
 * @property \Phalcon\Mvc\Model\Manager modelsManager
 * @property \Phalcon\Session\Adapter\Memcache session
 * @property \Phalcon\Http\Response\Cookies cookies
 * @property \Phalcon\Http\Request request
 * @property \Phalcon\Http\Response response
 * @property \Phalcon\Mvc\Router router
 * @property \Phalcon\Cache\Backend\Memcache cache
 * @property \Phalcon\Mvc\Url url
 * @property \Xnova\Models\User user
 * @property \Friday\Core\Auth\Auth auth
 * @property \Phalcon\Mvc\Dispatcher dispatcher
 * @property \Phalcon\Flash\Direct flash
 * @property \Phalcon\Registry|\stdClass storage
 * @property \Phalcon\Config|\stdClass config
 * @property \Xnova\Game game
 */
class Galaxy extends Component
{
	public function createPlanetByUserId ($user_id)
	{
		$Galaxy = Options::get('LastSettedGalaxyPos');
		$System = Options::get('LastSettedSystemPos');
		$Planet = Options::get('LastSettedPlanetPos');

		do
		{
			$free = self::getFreePositions($Galaxy, $System, round($this->config->game->maxPlanetInSystem * 0.2), round($this->config->game->maxPlanetInSystem * 0.8));

			if (count($free) > 0)
				$position = $free[array_rand($free)];
			else
				$position = 0;

            if ($position > 0 && $Planet < $this->config->game->get('maxRegPlanetsInSystem', 3))
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

		$PlanetID = $this->createPlanet($Galaxy, $System, $position, $user_id, _getText('sys_plnt_defaultname'), true);

		if ($PlanetID !== false)
		{
			Options::set('LastSettedGalaxyPos', $Galaxy);
			Options::set('LastSettedSystemPos', $System);
			Options::set('LastSettedPlanetPos', $Planet);

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
		if ($this->isPositionFree($Galaxy, $System, $Position))
		{
			$planet = $this->sizeRandomiser($Position, $HomeWorld, $Base);

			$planet->metal 		= $this->config->game->baseMetalProduction;
			$planet->crystal 	= $this->config->game->baseCristalProduction;
			$planet->deuterium 	= $this->config->game->baseDeuteriumProduction;

			$planet->galaxy = $Galaxy;
			$planet->system = $System;
			$planet->planet = $Position;

			$planet->planet_type = 1;

			if ($Base)
				$planet->planet_type = 5;

			$planet->id_owner = $PlanetOwnerID;
			$planet->last_update = time();
			$planet->name = ($PlanetName == '') ? _getText('sys_colo_defaultname') : $PlanetName;

			if ($planet->create())
			{
				if (isset($this->session) && $this->session->has('fleet_shortcut'))
					$this->session->remove('fleet_shortcut');

				return $planet->id;
			}
			else
				return false;
		}
		else
			return false;
	}

	public function createMoon ($Galaxy, $System, $Planet, $Owner, $Chance)
	{
		$planet = Planet::findByCoords($Galaxy, $System, $Planet, 1);

		if ($planet && $planet->parent_planet == 0)
		{
			$maxtemp = $planet->temp_max - rand(10, 45);
			$mintemp = $planet->temp_min - rand(10, 45);

			if ($Chance > 20)
				$Chance = 20;

			$size = floor(pow(mt_rand(10, 20) + 3 * $Chance, 0.5) * 1000);

			$moon = new Planet();
			$moon->create([
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

			if ($moon->id > 0)
			{
				$planet->parent_planet = $moon->id;
				$planet->update();

				return $moon->id;
			}
			else
				return false;
		}
		else
			return false;
	}

	public function isPositionFree ($galaxy, $system, $planet, $type = false)
	{
		if (!$galaxy || !$system || !$planet)
			return false;

		$exist = Planet::count('galaxy = '.$galaxy.' AND system = '.$system.' AND planet = '.$planet.''.($type !== false ? ' AND planet_type = '.$type : ''));

		return (!($exist > 0));
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

	public function sizeRandomiser ($Position, $HomeWorld = false, $Base = false)
	{
		$planetData = [];
		require(dirname(__DIR__).'/Vars/planet.php');

		$planet = new Planet;

		if ($HomeWorld)
			$planet->field_max = (int) $this->config->game->get('initial_fields', 163);
		elseif ($Base)
			$planet->field_max = (int) $this->config->game->get('initial_base_fields', 10);
		else
			$planet->field_max = (int) floor($planetData[$Position]['fields'] * (int) $this->config->game->get('planetFactor', 1));

		$planet->diameter = (int) floor(1000 * sqrt($planet->field_max));

		$planet->temp_max = $planetData[$Position]['temp'];
		$planet->temp_min = $planet->temp_max - 40;

		if ($Base)
			$planet->image = 'baseplanet01';
		else
		{
			$imageNames = array_keys($planetData[$Position]['image']);
			$imageNameType = $imageNames[array_rand($imageNames)];

			$planet->image  = $imageNameType;
			$planet->image .= 'planet';
			$planet->image .= $planetData[$Position]['image'][$imageNameType] < 10 ? '0' : '';
			$planet->image .= $planetData[$Position]['image'][$imageNameType];
		}

		return $planet;
	}
}