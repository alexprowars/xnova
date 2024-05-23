<?php

namespace App\Missions;

use App\Models\Planet;
use App\FleetEngine;
use App\Format;
use App\User;

class Stay extends FleetEngine implements Mission
{
	public function targetEvent()
	{
		$TargetPlanet = Planet::query()
			->where('galaxy', $this->fleet->end_galaxy)
			->where('system', $this->fleet->end_system)
			->where('planet', $this->fleet->end_planet)
			->where('planet_type', $this->fleet->end_type)
			->first();

		if (!$TargetPlanet || $TargetPlanet->user_id != $this->fleet->target_user_id) {
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

			User::sendMessage($this->fleet->target_user_id, 0, $this->fleet->start_time, 6, __('fleet_engine.sys_mess_qg'), $TargetMessage);
		}
	}

	public function endStayEvent()
	{
		return;
	}

	public function returnEvent()
	{
		$TargetPlanet = Planet::query()
			->where('galaxy', $this->fleet->start_galaxy)
			->where('system', $this->fleet->start_system)
			->where('planet', $this->fleet->start_planet)
			->where('planet_type', $this->fleet->start_type)
			->first();

		if (!$TargetPlanet || $TargetPlanet->user_id != $this->fleet->user_id) {
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

			User::sendMessage($this->fleet->user_id, 0, $this->fleet->end_time, 6, __('fleet_engine.sys_mess_qg'), $TargetMessage);
		}
	}
}
