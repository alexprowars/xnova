<?php

namespace Xnova\Missions;

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Support\Facades\DB;
use Xnova\FleetEngine;
use Xnova\Format;
use Xnova\User;

class Stay extends FleetEngine implements Mission
{
	public function TargetEvent()
	{
		$TargetPlanet = DB::selectOne("SELECT id_owner FROM planets WHERE galaxy = '" . $this->fleet->end_galaxy . "' AND system = '" . $this->fleet->end_system . "' AND planet = '" . $this->fleet->end_planet . "' AND planet_type = '" . $this->fleet->end_type . "'");

		if ($TargetPlanet->id_owner != $this->fleet->target_owner) {
			$this->ReturnFleet();
		} else {
			$this->RestoreFleetToPlanet(false);
			$this->KillFleet();

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

	public function EndStayEvent()
	{
		return;
	}

	public function ReturnEvent()
	{
		$TargetPlanet = DB::selectOne("SELECT id_owner FROM planets WHERE galaxy = '" . $this->fleet->start_galaxy . "' AND system = '" . $this->fleet->start_system . "' AND planet = '" . $this->fleet->start_planet . "' AND planet_type = '" . $this->fleet->start_type . "';");

		if ($TargetPlanet->id_owner != $this->fleet->owner) {
			$this->KillFleet();
		} else {
			$this->RestoreFleetToPlanet();
			$this->KillFleet();

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
