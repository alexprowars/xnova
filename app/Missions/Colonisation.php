<?php

namespace Xnova\Missions;

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Xnova\FleetEngine;
use Xnova\Galaxy;
use Xnova\Models;
use Xnova\User;

class Colonisation extends FleetEngine implements Mission
{
	public function TargetEvent()
	{
		/** @var User $owner */
		$owner = User::query()->find($this->fleet->owner);

		$maxPlanets = $owner->getTechLevel('colonisation') + 1;

		if ($maxPlanets > Config::get('settings.maxPlanets', 9)) {
			$maxPlanets = Config::get('settings.maxPlanets', 9);
		}

		$galaxy = new Galaxy();

		$iPlanetCount = Models\Planets::query()
			->where('id_owner', $this->fleet->owner)
			->where('planet_type', 1)
			->count();

		$TargetAdress = sprintf(__('fleet_engine.sys_adress_planet'), $this->fleet->end_galaxy, $this->fleet->end_system, $this->fleet->end_planet);

		if ($galaxy->isPositionFree($this->fleet->end_galaxy, $this->fleet->end_system, $this->fleet->end_planet)) {
			if ($iPlanetCount >= $maxPlanets) {
				$TheMessage = __('fleet_engine.sys_colo_arrival') . $TargetAdress . __('fleet_engine.sys_colo_maxcolo') . $maxPlanets . __('fleet_engine.sys_colo_planet');

				User::sendMessage($this->fleet->owner, 0, $this->fleet->start_time, 0, __('fleet_engine.sys_colo_mess_from'), $TheMessage);

				$this->ReturnFleet();
			} else {
				$NewOwnerPlanet = $galaxy->createPlanet($this->fleet->end_galaxy, $this->fleet->end_system, $this->fleet->end_planet, $this->fleet->owner, __('fleet_engine.sys_colo_defaultname'), false);

				if ($NewOwnerPlanet !== false) {
					$TheMessage = __('fleet_engine.sys_colo_arrival') . $TargetAdress . __('fleet_engine.sys_colo_allisok');

					User::sendMessage($this->fleet->owner, 0, $this->fleet->start_time, 0, __('fleet_engine.sys_colo_mess_from'), $TheMessage);

					$newFleet = [];

					$fleetData = $this->fleet->getShips();

					foreach ($fleetData as $shipId => $shipArr) {
						if ($shipId == 208 && $shipArr['count'] > 0) {
							$shipArr['count']--;
						}

						$newFleet[] = $shipArr;
					}

					$this->fleet->fleet_array = $newFleet;

					$this->RestoreFleetToPlanet(false);
					$this->KillFleet();

					Cache::forget('app::planetlist_' . $this->fleet->owner);
				} else {
					$this->ReturnFleet();

					$TheMessage = __('fleet_engine.sys_colo_arrival') . $TargetAdress . __('fleet_engine.sys_colo_badpos');

					User::sendMessage($this->fleet->owner, 0, $this->fleet->start_time, 0, __('fleet_engine.sys_colo_mess_from'), $TheMessage);
				}
			}
		} else {
			$this->ReturnFleet();

			$TheMessage = __('fleet_engine.sys_colo_arrival') . $TargetAdress . __('fleet_engine.sys_colo_notfree');

			User::sendMessage($this->fleet->owner, 0, $this->fleet->end_time, 0, __('fleet_engine.sys_colo_mess_from'), $TheMessage);
		}
	}

	public function EndStayEvent()
	{
		return;
	}

	public function ReturnEvent()
	{
		$this->RestoreFleetToPlanet();
		$this->KillFleet();
	}
}
