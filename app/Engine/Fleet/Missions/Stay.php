<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Enums\MessageType;
use App\Format;
use App\Models\Planet;
use App\Notifications\MessageNotification;

class Stay extends BaseMission
{
	public function targetEvent()
	{
		$targetPlanet = Planet::findByCoordinates($this->fleet->getDestinationCoordinates());

		if (!$targetPlanet || $targetPlanet->user_id != $this->fleet->target_user_id) {
			$this->return();
		} else {
			$this->restoreFleetToPlanet(false);
			$this->killFleet();

			$TargetAddedGoods = '';

			$fleetData = $this->fleet->getShips();

			foreach ($fleetData as $shipId => $shipArr) {
				$TargetAddedGoods .= ', ' . __('main.tech.' . $shipId) . ': ' . $shipArr['count'];
			}

			$TargetMessage = sprintf(
				__('fleet_engine.sys_stat_mess'),
				$this->fleet->getTargetAdressLink(),
				Format::number($this->fleet->resource_metal),
				__('main.Metal'),
				Format::number($this->fleet->resource_crystal),
				__('main.Crystal'),
				Format::number($this->fleet->resource_deuterium),
				__('main.Deuterium')
			);

			if ($TargetAddedGoods != '') {
				$TargetMessage .= '<br>' . trim(substr($TargetAddedGoods, 1));
			}

			$this->fleet->target->notify(new MessageNotification(null, MessageType::Fleet, __('fleet_engine.sys_mess_qg'), $TargetMessage));
		}
	}

	public function returnEvent()
	{
		$targetPlanet = Planet::findByCoordinates($this->fleet->getOriginCoordinates());

		if (!$targetPlanet || $targetPlanet->user_id != $this->fleet->user_id) {
			$this->killFleet();
		} else {
			$this->restoreFleetToPlanet();
			$this->killFleet();

			$TargetAddedGoods = sprintf(__('fleet_engine.sys_stay_mess_goods'), __('main.Metal'), Format::number($this->fleet->resource_metal), __('main.Crystal'), Format::number($this->fleet->resource_crystal), __('main.Deuterium'), Format::number($this->fleet->resource_deuterium));

			$fleetData = $this->fleet->getShips();

			foreach ($fleetData as $shipId => $shipArr) {
				$TargetAddedGoods .= ', ' . __('main.tech.' . $shipId) . ': ' . $shipArr['count'];
			}

			$TargetMessage = __('fleet_engine.sys_stay_mess_back') . $this->fleet->getTargetAdressLink() . __('fleet_engine.sys_stay_mess_bend') . "<br />" . $TargetAddedGoods;

			$this->fleet->user->notify(new MessageNotification(null, MessageType::Fleet, __('fleet_engine.sys_mess_qg'), $TargetMessage));
		}
	}
}
