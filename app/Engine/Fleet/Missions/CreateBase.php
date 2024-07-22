<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Enums\MessageType;
use App\Engine\Enums\PlanetType;
use App\Engine\Fleet\FleetEngine;
use App\Engine\Galaxy;
use App\Models;
use App\Notifications\MessageNotification;
use Illuminate\Support\Facades\Cache;

class CreateBase extends FleetEngine implements Mission
{
	public function targetEvent()
	{
		// Определяем максимальное количество баз
		$maxBases = $this->fleet->user->getTechLevel('fleet_base');

		$galaxy = new Galaxy();

		// Получение общего количества построенных баз
		$iPlanetCount = Models\Planet::query()
			->where('user_id', $this->fleet->user_id)
			->where('planet_type', PlanetType::MILITARY_BASE)
			->count();

		$TargetAdress = sprintf(__('fleet_engine.sys_adress_planet'), $this->fleet->end_galaxy, $this->fleet->end_system, $this->fleet->end_planet);

		// Если в галактике пусто (планета не заселена)
		if ($galaxy->isPositionFree($this->fleet->getDestinationCoordinates())) {
			// Если лимит баз исчерпан
			if ($iPlanetCount >= $maxBases) {
				$TheMessage = __('fleet_engine.sys_colo_arrival') . $TargetAdress . __('fleet_engine.sys_colo_maxcolo') . $maxBases . __('fleet_engine.sys_base_planet');

				$this->fleet->user->notify(new MessageNotification(null, MessageType::Fleet, __('fleet_engine.sys_base_mess_from'), $TheMessage));
				$this->fleet->return();
			} else {
				// Создание планеты-базы
				$NewOwnerPlanet = $galaxy->createPlanet(
					$this->fleet->getDestinationCoordinates(),
					$this->fleet->user_id,
					__('fleet_engine.sys_base_defaultname'),
				);

				// Если планета-база создана
				if ($NewOwnerPlanet) {
					$TheMessage = __('fleet_engine.sys_colo_arrival') . $TargetAdress . __('fleet_engine.sys_base_allisok');

					$this->fleet->user->notify(new MessageNotification(null, MessageType::Fleet, __('fleet_engine.sys_base_mess_from'), $TheMessage));

					$newFleet = [];

					$fleetData = $this->fleet->getShips();

					foreach ($fleetData as $shipId => $shipArr) {
						if ($shipId == 216 && $shipArr['count'] > 0) {
							$shipArr['count']--;
						}

						$newFleet[] = $shipArr;
					}

					$this->fleet->fleet_array = $newFleet;
					$this->fleet->end_type = PlanetType::MILITARY_BASE;

					$this->restoreFleetToPlanet(false);
					$this->killFleet();

					Cache::forget('app::planetlist_' . $this->fleet->user_id);
				} else {
					$this->fleet->return();

					$TheMessage = __('fleet_engine.sys_colo_arrival') . $TargetAdress . __('fleet_engine.sys_base_badpos');

					$this->fleet->user->notify(new MessageNotification(null, MessageType::Fleet, __('fleet_engine.sys_base_mess_from'), $TheMessage));
				}
			}
		} else {
			$this->fleet->return();

			$TheMessage = __('fleet_engine.sys_colo_arrival') . $TargetAdress . __('fleet_engine.sys_base_notfree');

			$this->fleet->user->notify(new MessageNotification(null, MessageType::Fleet, __('fleet_engine.sys_base_mess_from'), $TheMessage));
		}
	}

	public function endStayEvent()
	{
	}

	public function returnEvent()
	{
		$this->restoreFleetToPlanet();
		$this->killFleet();
	}
}
