<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Coordinates;
use App\Engine\Enums\MessageType;
use App\Engine\Enums\PlanetType;
use App\Facades\Galaxy;
use App\Models;
use App\Models\Planet;
use App\Notifications\MessageNotification;
use Illuminate\Support\Facades\Cache;

class CreateBase extends BaseMission
{
	public function isMissionPossible(Planet $planet, Coordinates $target, ?Planet $targetPlanet, array $units = [], bool $isAssault = false): bool
	{
		return !empty($units[216]) && !$targetPlanet && $target->getType() == PlanetType::PLANET;
	}

	public function targetEvent()
	{
		// Определяем максимальное количество баз
		$maxBases = $this->fleet->user->getTechLevel('fleet_base');

		// Получение общего количества построенных баз
		$iPlanetCount = Models\Planet::query()
			->whereBelongsTo($this->fleet->user)
			->where('planet_type', PlanetType::MILITARY_BASE)
			->count();

		$TargetAdress = __('main.sys_adress_planet', [
			'galaxy' => $this->fleet->end_galaxy,
			'system' => $this->fleet->end_system,
			'planet' => $this->fleet->end_planet,
		]);

		// Если в галактике пусто (планета не заселена)
		if (Galaxy::isPositionFree($this->fleet->getDestinationCoordinates())) {
			// Если лимит баз исчерпан
			if ($iPlanetCount >= $maxBases) {
				$TheMessage = __('fleet_engine.sys_colo_arrival') . $TargetAdress . __('fleet_engine.sys_colo_maxcolo') . $maxBases . __('fleet_engine.sys_base_planet');

				$this->fleet->user->notify(new MessageNotification(null, MessageType::Fleet, __('fleet_engine.sys_base_mess_from'), $TheMessage));
				$this->return();
			} else {
				// Создание планеты-базы
				$NewOwnerPlanet = Galaxy::createPlanet(
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
					$this->return();

					$TheMessage = __('fleet_engine.sys_colo_arrival') . $TargetAdress . __('fleet_engine.sys_base_badpos');

					$this->fleet->user->notify(new MessageNotification(null, MessageType::Fleet, __('fleet_engine.sys_base_mess_from'), $TheMessage));
				}
			}
		} else {
			$this->return();

			$TheMessage = __('fleet_engine.sys_colo_arrival') . $TargetAdress . __('fleet_engine.sys_base_notfree');

			$this->fleet->user->notify(new MessageNotification(null, MessageType::Fleet, __('fleet_engine.sys_base_mess_from'), $TheMessage));
		}
	}
}
