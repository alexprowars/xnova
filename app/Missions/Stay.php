<?php

namespace App\Missions;

use Illuminate\Support\Facades\DB;
use App\FleetEngine;
use App\Format;
use App\User;

class Stay extends FleetEngine implements Mission
{
	public function targetEvent()
	{
		$TargetPlanet = DB::selectOne("SELECT id_owner FROM planets WHERE galaxy = '" . $this->fleet->end_galaxy . "' AND system = '" . $this->fleet->end_system . "' AND planet = '" . $this->fleet->end_planet . "' AND planet_type = '" . $this->fleet->end_type . "'");

		if ($TargetPlanet->id_owner != $this->fleet->target_owner) {
			$this->returnFleet();
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

			User::sendMessage($this->fleet->target_owner, 0, $this->fleet->start_time, 5, __('fleet_engine.sys_mess_qg'), $TargetMessage);
		}
	}

	public function endStayEvent()
	{
		return;
	}

	public function returnEvent()
	{
		$TargetPlanet = DB::selectOne("SELECT id_owner FROM planets WHERE galaxy = '" . $this->fleet->start_galaxy . "' AND system = '" . $this->fleet->start_system . "' AND planet = '" . $this->fleet->start_planet . "' AND planet_type = '" . $this->fleet->start_type . "';");

		if ($TargetPlanet->id_owner != $this->fleet->owner) {
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

			User::sendMessage($this->fleet->owner, 0, $this->fleet->end_time, 5, __('fleet_engine.sys_mess_qg'), $TargetMessage);
		}
	}
}
