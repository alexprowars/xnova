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

			$targetMessage = __('fleet_engine.sys_stat_mess', [
				'target' => $this->fleet->getTargetAdressLink(),
				'm' => Format::number($this->fleet->resource_metal),
				'mt' => __('main.metal'),
				'c' => Format::number($this->fleet->resource_crystal),
				'ct' => __('main.crystal'),
				'd' => Format::number($this->fleet->resource_deuterium),
				'dt' => __('main.deuterium')
			]);

			if ($TargetAddedGoods != '') {
				$targetMessage .= '<br>' . trim(substr($TargetAddedGoods, 1));
			}

			$this->fleet->target->notify(new MessageNotification(null, MessageType::Fleet, __('fleet_engine.sys_mess_qg'), $targetMessage));
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

			$targetAddedGoods = __('fleet_engine.sys_stay_mess_goods', [
				'mt' => __('main.metal'),
				'm' => Format::number($this->fleet->resource_metal),
				'ct' => __('main.crystal'),
				'c' => Format::number($this->fleet->resource_crystal),
				'dt' => __('main.deuterium'),
				'd' => Format::number($this->fleet->resource_deuterium),
			]);

			$fleetData = $this->fleet->getShips();

			foreach ($fleetData as $shipId => $shipArr) {
				$targetAddedGoods .= ', ' . __('main.tech.' . $shipId) . ': ' . $shipArr['count'];
			}

			$TargetMessage = __('fleet_engine.sys_stay_mess_back') . $this->fleet->getTargetAdressLink() . __('fleet_engine.sys_stay_mess_bend') . "<br />" . $targetAddedGoods;

			$this->fleet->user->notify(new MessageNotification(null, MessageType::Fleet, __('fleet_engine.sys_mess_qg'), $TargetMessage));
		}
	}
}
