<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Enums\MessageType;
use App\Engine\Enums\PlanetType;
use App\Engine\Galaxy;
use App\Models;
use App\Notifications\MessageNotification;
use Illuminate\Support\Facades\Cache;

class Colonization extends BaseMission
{
	public function targetEvent()
	{
		$maxPlanets = $this->fleet->user->getTechLevel('colonization') + 1;

		if ($maxPlanets > config('game.maxPlanets', 9)) {
			$maxPlanets = config('game.maxPlanets', 9);
		}

		$galaxy = new Galaxy();

		$iPlanetCount = Models\Planet::query()
			->whereBelongsTo($this->fleet->user)
			->where('planet_type', PlanetType::PLANET)
			->count();

		$TargetAdress = sprintf(__('fleet_engine.sys_adress_planet'), $this->fleet->end_galaxy, $this->fleet->end_system, $this->fleet->end_planet);

		if ($galaxy->isPositionFree($this->fleet->getDestinationCoordinates())) {
			if ($iPlanetCount >= $maxPlanets) {
				$TheMessage = __('fleet_engine.sys_colo_arrival') . $TargetAdress . __('fleet_engine.sys_colo_maxcolo') . $maxPlanets . __('fleet_engine.sys_colo_planet');

				$this->fleet->user->notify(new MessageNotification(null, MessageType::Fleet, __('fleet_engine.sys_colo_mess_from'), $TheMessage));
				$this->return();
			} else {
				$NewOwnerPlanet = $galaxy->createPlanet(
					$this->fleet->getDestinationCoordinates(),
					$this->fleet->user_id,
					__('fleet_engine.sys_colo_defaultname')
				);

				if ($NewOwnerPlanet) {
					$TheMessage = __('fleet_engine.sys_colo_arrival') . $TargetAdress . __('fleet_engine.sys_colo_allisok');

					$this->fleet->user->notify(new MessageNotification(null, MessageType::Fleet, __('fleet_engine.sys_colo_mess_from'), $TheMessage));

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
					$this->return();

					$TheMessage = __('fleet_engine.sys_colo_arrival') . $TargetAdress . __('fleet_engine.sys_colo_badpos');

					$this->fleet->user->notify(new MessageNotification(null, MessageType::Fleet, __('fleet_engine.sys_colo_mess_from'), $TheMessage));
				}
			}
		} else {
			$this->return();

			$TheMessage = __('fleet_engine.sys_colo_arrival') . $TargetAdress . __('fleet_engine.sys_colo_notfree');

			$this->fleet->user->notify(new MessageNotification(null, MessageType::Fleet, __('fleet_engine.sys_colo_mess_from'), $TheMessage));
		}
	}
}
