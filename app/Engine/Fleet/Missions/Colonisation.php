<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\FleetEngine;
use App\Engine\Galaxy;
use App\Models;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class Colonisation extends FleetEngine implements Mission
{
	public function targetEvent()
	{
		$owner = User::query()->find($this->fleet->user_id);

		$maxPlanets = $owner->getTechLevel('colonisation') + 1;

		if ($maxPlanets > config('settings.maxPlanets', 9)) {
			$maxPlanets = config('settings.maxPlanets', 9);
		}

		$galaxy = new Galaxy();

		$iPlanetCount = Models\Planet::query()
			->where('user_id', $this->fleet->user_id)
			->where('planet_type', 1)
			->count();

		$TargetAdress = sprintf(__('fleet_engine.sys_adress_planet'), $this->fleet->end_galaxy, $this->fleet->end_system, $this->fleet->end_planet);

		if ($galaxy->isPositionFree($this->fleet->getDestinationCoordinates())) {
			if ($iPlanetCount >= $maxPlanets) {
				$TheMessage = __('fleet_engine.sys_colo_arrival') . $TargetAdress . __('fleet_engine.sys_colo_maxcolo') . $maxPlanets . __('fleet_engine.sys_colo_planet');

				User::sendMessage($this->fleet->user_id, 0, $this->fleet->start_time, 1, __('fleet_engine.sys_colo_mess_from'), $TheMessage);

				$this->fleet->return();
			} else {
				$NewOwnerPlanet = $galaxy->createPlanet(
					$this->fleet->getDestinationCoordinates(),
					$this->fleet->user_id,
					__('fleet_engine.sys_colo_defaultname')
				);

				if ($NewOwnerPlanet !== false) {
					$TheMessage = __('fleet_engine.sys_colo_arrival') . $TargetAdress . __('fleet_engine.sys_colo_allisok');

					User::sendMessage($this->fleet->user_id, 0, $this->fleet->start_time, 1, __('fleet_engine.sys_colo_mess_from'), $TheMessage);

					$newFleet = [];

					$fleetData = $this->fleet->getShips();

					foreach ($fleetData as $shipId => $shipArr) {
						if ($shipId == 208 && $shipArr['count'] > 0) {
							$shipArr['count']--;
						}

						$newFleet[] = $shipArr;
					}

					$this->fleet->fleet_array = $newFleet;

					$this->restoreFleetToPlanet(false);
					$this->killFleet();

					Cache::forget('app::planetlist_' . $this->fleet->user_id);
				} else {
					$this->fleet->return();

					$TheMessage = __('fleet_engine.sys_colo_arrival') . $TargetAdress . __('fleet_engine.sys_colo_badpos');

					User::sendMessage($this->fleet->user_id, 0, $this->fleet->start_time, 1, __('fleet_engine.sys_colo_mess_from'), $TheMessage);
				}
			}
		} else {
			$this->fleet->return();

			$TheMessage = __('fleet_engine.sys_colo_arrival') . $TargetAdress . __('fleet_engine.sys_colo_notfree');

			User::sendMessage($this->fleet->user_id, 0, $this->fleet->end_time, 1, __('fleet_engine.sys_colo_mess_from'), $TheMessage);
		}
	}

	public function endStayEvent()
	{
		return;
	}

	public function returnEvent()
	{
		$this->restoreFleetToPlanet();
		$this->killFleet();
	}
}
