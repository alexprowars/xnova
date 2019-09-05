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

class Recycling extends FleetEngine implements Mission
{
	public function TargetEvent()
	{
		$TargetGalaxy = $this->db->query("SELECT id, debris_metal, debris_crystal FROM planets WHERE galaxy = '" . $this->_fleet->end_galaxy . "' AND system = '" . $this->_fleet->end_system . "' AND planet = '" . $this->_fleet->end_planet . "' AND planet_type != 3 LIMIT 1;")->fetch();

		if (isset($TargetGalaxy['id']))
		{
			$RecyclerCapacity = 0;
			$OtherFleetCapacity = 0;

			$fleetData = $this->_fleet->getShips();

			foreach ($fleetData as $shipId => $shipArr)
			{
				$capacity = $this->registry->CombatCaps[$shipId]["capacity"] * $shipArr['count'];

				if ($shipId == 209)
					$RecyclerCapacity += $capacity;
				else
					$OtherFleetCapacity += $capacity;
			}

			$IncomingFleetGoods = $this->_fleet->resource_metal + $this->_fleet->resource_crystal + $this->_fleet->resource_deuterium;

			// Если часть ресурсов хранится в переработчиках
			if ($IncomingFleetGoods > $OtherFleetCapacity)
				$RecyclerCapacity -= ($IncomingFleetGoods - $OtherFleetCapacity);

			if (($TargetGalaxy["debris_metal"] + $TargetGalaxy["debris_crystal"]) <= $RecyclerCapacity)
			{
				$RecycledGoods["metal"] = $TargetGalaxy["debris_metal"];
				$RecycledGoods["crystal"] = $TargetGalaxy["debris_crystal"];
			}
			else
			{
				if (($TargetGalaxy["debris_metal"] > $RecyclerCapacity / 2) AND ($TargetGalaxy["debris_crystal"] > $RecyclerCapacity / 2))
				{
					$RecycledGoods["metal"] = $RecyclerCapacity / 2;
					$RecycledGoods["crystal"] = $RecyclerCapacity / 2;
				}
				else
				{
					if ($TargetGalaxy["debris_metal"] > $TargetGalaxy["debris_crystal"])
					{
						$RecycledGoods["crystal"] = $TargetGalaxy["debris_crystal"];

						if ($TargetGalaxy["debris_metal"] > ($RecyclerCapacity - $RecycledGoods["crystal"]))
							$RecycledGoods["metal"] = $RecyclerCapacity - $RecycledGoods["crystal"];
						else
							$RecycledGoods["metal"] = $TargetGalaxy["debris_metal"];
					}
					else
					{
						$RecycledGoods["metal"] = $TargetGalaxy["debris_metal"];

						if ($TargetGalaxy["debris_crystal"] > ($RecyclerCapacity - $RecycledGoods["metal"]))
							$RecycledGoods["crystal"] = $RecyclerCapacity - $RecycledGoods["metal"];
						else
							$RecycledGoods["crystal"] = $TargetGalaxy["debris_crystal"];
					}
				}
			}

			DB::statement("UPDATE planets SET debris_metal = debris_metal - '" . $RecycledGoods["metal"] . "', debris_crystal = debris_crystal - '" . $RecycledGoods["crystal"] . "' WHERE id = '" . $TargetGalaxy['id'] . "' LIMIT 1;");

			$this->ReturnFleet(['+resource_metal' => $RecycledGoods["metal"], '+resource_crystal' => $RecycledGoods["crystal"]]);

			$Message = sprintf(__('fleet_engine.sys_recy_gotten'), Format::number($RecycledGoods["metal"]), __('main.Metal'), Format::number($RecycledGoods["crystal"]), __('main.Crystal'), $this->_fleet->getTargetAdressLink());
		}
		else
		{
			$this->ReturnFleet();

			$Message = sprintf(__('fleet_engine.sys_recy_gotten'), 0, __('main.Metal'), 0, __('main.Crystal'), $this->_fleet->getTargetAdressLink());
		}

		User::sendMessage($this->_fleet->owner, 0, $this->_fleet->start_time, 4, __('fleet_engine.sys_mess_spy_control'), $Message);
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