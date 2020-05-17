<?php

namespace Xnova\Missions;

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Support\Facades\Cache;
use Xnova\FleetEngine;
use Xnova\Galaxy;
use Xnova\User;
use Xnova\Models;

class CreateBase extends FleetEngine implements Mission
{
	public function TargetEvent()
	{
		/** @var User $owner */
		$owner = User::query()->find($this->fleet->owner);

		// Определяем максимальное количество баз
		$maxBases = $owner->getTechLevel('fleet_base');

		$galaxy = new Galaxy();

		// Получение общего количества построенных баз
		$iPlanetCount = Models\Planet::query()->where('id_owner', $this->fleet->owner)
			->where('planet_type', 5)->count();

		$TargetAdress = sprintf(__('fleet_engine.sys_adress_planet'), $this->fleet->end_galaxy, $this->fleet->end_system, $this->fleet->end_planet);

		// Если в галактике пусто (планета не заселена)
		if ($galaxy->isPositionFree($this->fleet->end_galaxy, $this->fleet->end_system, $this->fleet->end_planet)) {
			// Если лимит баз исчерпан
			if ($iPlanetCount >= $maxBases) {
				$TheMessage = __('fleet_engine.sys_colo_arrival') . $TargetAdress . __('fleet_engine.sys_colo_maxcolo') . $maxBases . __('fleet_engine.sys_base_planet');

				User::sendMessage($this->fleet->owner, 0, $this->fleet->start_time, 0, __('fleet_engine.sys_base_mess_from'), $TheMessage);

				$this->ReturnFleet();
			} else {
				// Создание планеты-базы
				$NewOwnerPlanet = $galaxy->createPlanet($this->fleet->end_galaxy, $this->fleet->end_system, $this->fleet->end_planet, $this->fleet->owner, __('fleet_engine.sys_base_defaultname'), false, true);

				// Если планета-база создана
				if ($NewOwnerPlanet !== false) {
					$TheMessage = __('fleet_engine.sys_colo_arrival') . $TargetAdress . __('fleet_engine.sys_base_allisok');

					User::sendMessage($this->fleet->owner, 0, $this->fleet->start_time, 0, __('fleet_engine.sys_base_mess_from'), $TheMessage);

					$newFleet = [];

					$fleetData = $this->fleet->getShips();

					foreach ($fleetData as $shipId => $shipArr) {
						if ($shipId == 216 && $shipArr['count'] > 0) {
							$shipArr['count']--;
						}

						$newFleet[] = $shipArr;
					}

					$this->fleet->fleet_array = $newFleet;
					$this->fleet->end_type = 5;

					$this->RestoreFleetToPlanet(false);
					$this->KillFleet();

					Cache::forget('app::planetlist_' . $this->fleet->owner);
				} else {
					$this->ReturnFleet();

					$TheMessage = __('fleet_engine.sys_colo_arrival') . $TargetAdress . __('fleet_engine.sys_base_badpos');

					User::sendMessage($this->fleet->owner, 0, $this->fleet->start_time, 0, __('fleet_engine.sys_base_mess_from'), $TheMessage);
				}
			}
		} else {
			$this->ReturnFleet();

			$TheMessage = __('fleet_engine.sys_colo_arrival') . $TargetAdress . __('fleet_engine.sys_base_notfree');

			User::sendMessage($this->fleet->owner, 0, $this->fleet->end_time, 0, __('fleet_engine.sys_base_mess_from'), $TheMessage);
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
