<?php

namespace App\Missions;

use Illuminate\Support\Facades\Cache;
use App\Entity\Coordinates;
use App\FleetEngine;
use App\Galaxy;
use App\Models;
use App\User;

class Colonisation extends FleetEngine implements Mission
{
	public function targetEvent()
	{
		$owner = User::query()->find($this->fleet->owner);

		$maxPlanets = $owner->getTechLevel('colonisation') + 1;

		if ($maxPlanets > config('settings.maxPlanets', 9)) {
			$maxPlanets = config('settings.maxPlanets', 9);
		}

		$galaxy = new Galaxy();

		$iPlanetCount = Models\Planet::query()
			->where('id_owner', $this->fleet->owner)
			->where('planet_type', 1)
			->count();

		$TargetAdress = sprintf(__('fleet_engine.sys_adress_planet'), $this->fleet->end_galaxy, $this->fleet->end_system, $this->fleet->end_planet);

		if ($galaxy->isPositionFree($this->fleet->getDestinationCoordinates())) {
			if ($iPlanetCount >= $maxPlanets) {
				$TheMessage = __('fleet_engine.sys_colo_arrival') . $TargetAdress . __('fleet_engine.sys_colo_maxcolo') . $maxPlanets . __('fleet_engine.sys_colo_planet');

				User::sendMessage($this->fleet->owner, 0, $this->fleet->start_time, 0, __('fleet_engine.sys_colo_mess_from'), $TheMessage);

				$this->returnFleet();
			} else {
				$NewOwnerPlanet = $galaxy->createPlanet(
					$this->fleet->getDestinationCoordinates(),
					$this->fleet->owner,
					__('fleet_engine.sys_colo_defaultname')
				);

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

					$this->restoreFleetToPlanet(false);
					$this->killFleet();

					Cache::forget('app::planetlist_' . $this->fleet->owner);
				} else {
					$this->returnFleet();

					$TheMessage = __('fleet_engine.sys_colo_arrival') . $TargetAdress . __('fleet_engine.sys_colo_badpos');

					User::sendMessage($this->fleet->owner, 0, $this->fleet->start_time, 0, __('fleet_engine.sys_colo_mess_from'), $TheMessage);
				}
			}
		} else {
			$this->returnFleet();

			$TheMessage = __('fleet_engine.sys_colo_arrival') . $TargetAdress . __('fleet_engine.sys_colo_notfree');

			User::sendMessage($this->fleet->owner, 0, $this->fleet->end_time, 0, __('fleet_engine.sys_colo_mess_from'), $TheMessage);
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
