<?php

namespace App\Missions;

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Support\Facades\DB;
use App\FleetEngine;
use App\Format;
use App\User;

class Recycling extends FleetEngine implements Mission
{
	public function targetEvent()
	{
		$TargetGalaxy = $this->db->query("SELECT id, debris_metal, debris_crystal FROM planets WHERE galaxy = '" . $this->fleet->end_galaxy . "' AND system = '" . $this->fleet->end_system . "' AND planet = '" . $this->fleet->end_planet . "' AND planet_type != 3 LIMIT 1;")->fetch();

		if (isset($TargetGalaxy['id'])) {
			$RecyclerCapacity = 0;
			$OtherFleetCapacity = 0;

			$fleetData = $this->fleet->getShips();

			foreach ($fleetData as $shipId => $shipArr) {
				$capacity = $this->registry->CombatCaps[$shipId]["capacity"] * $shipArr['count'];

				if ($shipId == 209) {
					$RecyclerCapacity += $capacity;
				} else {
					$OtherFleetCapacity += $capacity;
				}
			}

			$IncomingFleetGoods = $this->fleet->resource_metal + $this->fleet->resource_crystal + $this->fleet->resource_deuterium;

			// Если часть ресурсов хранится в переработчиках
			if ($IncomingFleetGoods > $OtherFleetCapacity) {
				$RecyclerCapacity -= ($IncomingFleetGoods - $OtherFleetCapacity);
			}

			if (($TargetGalaxy["debris_metal"] + $TargetGalaxy["debris_crystal"]) <= $RecyclerCapacity) {
				$RecycledGoods["metal"] = $TargetGalaxy["debris_metal"];
				$RecycledGoods["crystal"] = $TargetGalaxy["debris_crystal"];
			} else {
				if (($TargetGalaxy["debris_metal"] > $RecyclerCapacity / 2) and ($TargetGalaxy["debris_crystal"] > $RecyclerCapacity / 2)) {
					$RecycledGoods["metal"] = $RecyclerCapacity / 2;
					$RecycledGoods["crystal"] = $RecyclerCapacity / 2;
				} else {
					if ($TargetGalaxy["debris_metal"] > $TargetGalaxy["debris_crystal"]) {
						$RecycledGoods["crystal"] = $TargetGalaxy["debris_crystal"];

						if ($TargetGalaxy["debris_metal"] > ($RecyclerCapacity - $RecycledGoods["crystal"])) {
							$RecycledGoods["metal"] = $RecyclerCapacity - $RecycledGoods["crystal"];
						} else {
							$RecycledGoods["metal"] = $TargetGalaxy["debris_metal"];
						}
					} else {
						$RecycledGoods["metal"] = $TargetGalaxy["debris_metal"];

						if ($TargetGalaxy["debris_crystal"] > ($RecyclerCapacity - $RecycledGoods["metal"])) {
							$RecycledGoods["crystal"] = $RecyclerCapacity - $RecycledGoods["metal"];
						} else {
							$RecycledGoods["crystal"] = $TargetGalaxy["debris_crystal"];
						}
					}
				}
			}

			DB::statement("UPDATE planets SET debris_metal = debris_metal - '" . $RecycledGoods["metal"] . "', debris_crystal = debris_crystal - '" . $RecycledGoods["crystal"] . "' WHERE id = '" . $TargetGalaxy['id'] . "' LIMIT 1;");

			$this->returnFleet(['+resource_metal' => $RecycledGoods["metal"], '+resource_crystal' => $RecycledGoods["crystal"]]);

			$Message = sprintf(__('fleet_engine.sys_recy_gotten'), Format::number($RecycledGoods["metal"]), __('main.Metal'), Format::number($RecycledGoods["crystal"]), __('main.Crystal'), $this->fleet->getTargetAdressLink());
		} else {
			$this->returnFleet();

			$Message = sprintf(__('fleet_engine.sys_recy_gotten'), 0, __('main.Metal'), 0, __('main.Crystal'), $this->fleet->getTargetAdressLink());
		}

		User::sendMessage($this->fleet->owner, 0, $this->fleet->start_time, 4, __('fleet_engine.sys_mess_spy_control'), $Message);
	}

	public function endStayEvent()
	{
		return;
	}

	public function returnEvent()
	{
		$this->restoreFleetToPlanet();
		$this->killFleet();
	}
}
