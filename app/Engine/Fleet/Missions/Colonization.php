<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Coordinates;
use App\Engine\Enums\MessageType;
use App\Engine\Enums\PlanetType;
use App\Facades\Galaxy;
use App\Models;
use App\Models\Planet;
use App\Notifications\MessageNotification;

class Colonization extends BaseMission
{
	public function isMissionPossible(Planet $planet, Coordinates $target, ?Planet $targetPlanet, array $units = [], bool $isAssault = false): bool
	{
		return !empty($units[208]) && $targetPlanet;
	}

	public function targetEvent()
	{
		$maxPlanets = $this->fleet->user->getTechLevel('colonization') + 1;

		if ($maxPlanets > config('game.maxPlanets', 9)) {
			$maxPlanets = config('game.maxPlanets', 9);
		}

		$targetAdress = __('main.sys_adress_planet', [
			'galaxy' => $this->fleet->end_galaxy,
			'system' => $this->fleet->end_system,
			'planet' => $this->fleet->end_planet,
		]);

		if (Galaxy::isPositionFree($this->fleet->getDestinationCoordinates())) {
			$iPlanetCount = Models\Planet::query()
				->whereBelongsTo($this->fleet->user)
				->where('planet_type', PlanetType::PLANET)
				->count();

			if ($iPlanetCount >= $maxPlanets) {
				$message = __('fleet_engine.sys_colo_arrival') . $targetAdress . __('fleet_engine.sys_colo_maxcolo') . $maxPlanets . __('fleet_engine.sys_colo_planet');

				$this->fleet->user->notify(new MessageNotification(null, MessageType::Fleet, __('fleet_engine.sys_colo_mess_from'), $message));
				$this->return();
			} else {
				$newOwnerPlanet = Galaxy::createPlanet(
					$this->fleet->getDestinationCoordinates(),
					$this->fleet->user_id,
					__('fleet_engine.sys_colo_defaultname')
				);

				if ($newOwnerPlanet) {
					$message = __('fleet_engine.sys_colo_arrival') . $targetAdress . __('fleet_engine.sys_colo_allisok');

					$this->fleet->user->notify(new MessageNotification(null, MessageType::Fleet, __('fleet_engine.sys_colo_mess_from'), $message));

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

					cache()->forget('app::planetlist_' . $this->fleet->user_id);
				} else {
					$this->return();

					$message = __('fleet_engine.sys_colo_arrival') . $targetAdress . __('fleet_engine.sys_colo_badpos');

					$this->fleet->user->notify(new MessageNotification(null, MessageType::Fleet, __('fleet_engine.sys_colo_mess_from'), $message));
				}
			}
		} else {
			$this->return();

			$message = __('fleet_engine.sys_colo_arrival') . $targetAdress . __('fleet_engine.sys_colo_notfree');

			$this->fleet->user->notify(new MessageNotification(null, MessageType::Fleet, __('fleet_engine.sys_colo_mess_from'), $message));
		}
	}
}
