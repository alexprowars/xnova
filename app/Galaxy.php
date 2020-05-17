<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace Xnova;

use Backpack\Settings\app\Models\Setting;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Xnova\Models\Planet;
use Xnova\Models\User as UserModel;

class Galaxy
{
	public function createPlanetByUserId($userId)
	{
		$Galaxy = Setting::get('LastSettedGalaxyPos');
		$System = Setting::get('LastSettedSystemPos');
		$Planet = Setting::get('LastSettedPlanetPos');

		do {
			$free = self::getFreePositions($Galaxy, $System, round(Config::get('settings.maxPlanetInSystem') * 0.2), round(Config::get('settings.maxPlanetInSystem') * 0.8));

			if (count($free) > 0) {
				$position = $free[array_rand($free)];
			} else {
				$position = 0;
			}

			if ($position > 0 && $Planet < Config::get('settings.maxRegPlanetsInSystem', 3)) {
				$Planet += 1;
			} else {
				$Planet = 1;

				if ($System >= Config::get('settings.maxSystemInGalaxy')) {
					$System = 1;

					if ($Galaxy >= Config::get('settings.maxGalaxyInWorld')) {
						$Galaxy = 1;
					} else {
						$Galaxy += 1;
					}
				} else {
					$System += 1;
				}
			}
		} while ($this->isPositionFree($Galaxy, $System, $position) === false);

		$PlanetID = $this->createPlanet($Galaxy, $System, $position, $userId, __('main.sys_plnt_defaultname'), true);

		if ($PlanetID !== false) {
			Setting::set('LastSettedGalaxyPos', $Galaxy);
			Setting::set('LastSettedSystemPos', $System);
			Setting::set('LastSettedPlanetPos', $Planet);

			$user = UserModel::find($userId);
			$user->update([
				'planet_id'		 => $PlanetID,
				'planet_current' => $PlanetID,
				'galaxy'		 => $Galaxy,
				'system'		 => $System,
				'planet'		 => $position
			]);

			return $PlanetID;
		} else {
			return false;
		}
	}

	public function createPlanet($Galaxy, $System, $Position, $PlanetOwnerID, $PlanetName = '', $HomeWorld = false, $Base = false)
	{
		if ($this->isPositionFree($Galaxy, $System, $Position)) {
			$planet = $this->sizeRandomiser($Position, $HomeWorld, $Base);

			$planet->metal 		= Config::get('settings.baseMetalProduction');
			$planet->crystal 	= Config::get('settings.baseCristalProduction');
			$planet->deuterium 	= Config::get('settings.baseDeuteriumProduction');

			$planet->galaxy = $Galaxy;
			$planet->system = $System;
			$planet->planet = $Position;

			$planet->planet_type = 1;

			if ($Base) {
				$planet->planet_type = 5;
			}

			$planet->id_owner = $PlanetOwnerID;
			$planet->last_update = time();
			$planet->name = ($PlanetName == '') ? __('main.sys_colo_defaultname') : $PlanetName;

			if ($planet->save()) {
				if (Session::has('fleet_shortcut')) {
					Session::remove('fleet_shortcut');
				}

				return $planet->id;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function createMoon($Galaxy, $System, $Planet, $Owner, $Chance)
	{
		$planet = Planet::findByCoords($Galaxy, $System, $Planet, 1);

		if ($planet && $planet->parent_planet == 0) {
			$maxtemp = $planet->temp_max - rand(10, 45);
			$mintemp = $planet->temp_min - rand(10, 45);

			if ($Chance > 20) {
				$Chance = 20;
			}

			$size = floor(pow(mt_rand(10, 20) + 3 * $Chance, 0.5) * 1000);

			$moon = new Planet();
			$moon->fill([
				'name' 			=> __('main.sys_moon'),
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
			$moon->save();

			if ($moon->id > 0) {
				$planet->parent_planet = $moon->id;
				$planet->update();

				return $moon->id;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function isPositionFree($galaxy, $system, $planet, $type = false)
	{
		if (!$galaxy || !$system || !$planet) {
			return false;
		}

		$exist = Planet::query()->where('galaxy', $galaxy)
			->where('system', $system)
			->where('planet', $planet);

		if ($type !== false) {
			$exist->where('planet_type', $type);
		}

		$exist = $exist->count();

		return (!($exist > 0));
	}

	public function getFreePositions($galaxy, $system, $start = 1, $end = 15)
	{
		$search = Planet::query()->select('id, planet')
			->where('galaxy', $galaxy)
			->where('system', $system)
			->where('planet', '>=', $start)
			->where('planet', '<=', $end);

		$planets = [];

		foreach ($search as $item) {
			$planets[$item->planet] = $item;
		}

		$result = [];

		for ($i = $start; $i <= $end; $i++) {
			if (!isset($planets[$i])) {
				$result[] = $i;
			}
		}

		return $result;
	}

	public function sizeRandomiser($Position, $HomeWorld = false, $Base = false)
	{
		$planetData = [];
		require(app_path('Vars/planet.php'));

		$planet = new Planet();

		if ($HomeWorld) {
			$planet->field_max = (int) Config::get('settings.initial_fields', 163);
		} elseif ($Base) {
			$planet->field_max = (int) Config::get('settings.initial_base_fields', 10);
		} else {
			$planet->field_max = (int) floor($planetData[$Position]['fields'] * (int) Config::get('settings.planetFactor', 1));
		}

		$planet->diameter = (int) floor(1000 * sqrt($planet->field_max));

		$planet->temp_max = $planetData[$Position]['temp'];
		$planet->temp_min = $planet->temp_max - 40;

		if ($Base) {
			$planet->image = 'baseplanet01';
		} else {
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
