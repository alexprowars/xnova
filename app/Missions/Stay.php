<?php

namespace Xnova\Missions;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\FleetEngine;
use Xnova\Format;
use Xnova\User;

class Stay extends FleetEngine implements Mission
{
	public function TargetEvent()
	{
		$TargetPlanet = $this->db->query("SELECT id_owner FROM planets WHERE galaxy = '" . $this->_fleet->end_galaxy . "' AND system = '" . $this->_fleet->end_system . "' AND planet = '" . $this->_fleet->end_planet . "' AND planet_type = '" . $this->_fleet->end_type . "'")->fetch();

		if ($TargetPlanet['id_owner'] != $this->_fleet->target_owner)
			$this->ReturnFleet();
		else
		{
			$this->RestoreFleetToPlanet(false);
			$this->KillFleet();

			$TargetAddedGoods = '';

			$fleetData = $this->_fleet->getShips();

			foreach ($fleetData as $shipId => $shipArr)
			{
				$TargetAddedGoods .= ', ' . __('main.tech.'.$shipId) . ': ' . $shipArr['count'];
			}

			$TargetMessage = sprintf(__('fleet_engine.sys_stat_mess'),
								$this->_fleet->getTargetAdressLink(),
								Format::number($this->_fleet->resource_metal), __('main.Metal'),
								Format::number($this->_fleet->resource_crystal), __('main.Crystal'),
								Format::number($this->_fleet->resource_deuterium), __('main.Deuterium'));

			if ($TargetAddedGoods != '')
				$TargetMessage .= '<br>'.trim(substr($TargetAddedGoods, 1));

			User::sendMessage($this->_fleet->target_owner, 0, $this->_fleet->start_time, 5, __('fleet_engine.sys_mess_qg'), $TargetMessage);
		}
	}

	public function EndStayEvent()
	{
		return;
	}

	public function ReturnEvent()
	{
		$TargetPlanet = $this->db->query("SELECT id_owner FROM planets WHERE galaxy = '" . $this->_fleet->start_galaxy . "' AND system = '" . $this->_fleet->start_system . "' AND planet = '" . $this->_fleet->start_planet . "' AND planet_type = '" . $this->_fleet->start_type . "';")->fetch();

		if ($TargetPlanet['id_owner'] != $this->_fleet->owner)
			$this->KillFleet();
		else
		{
			$this->RestoreFleetToPlanet();
			$this->KillFleet();

			$TargetAddedGoods = sprintf(__('fleet_engine.sys_stay_mess_goods'), __('main.Metal'), Format::number($this->_fleet->resource_metal), __('main.Crystal'), Format::number($this->_fleet->resource_crystal), __('main.Deuterium'), Format::number($this->_fleet->resource_deuterium));

			$fleetData = $this->_fleet->getShips();

			foreach ($fleetData as $shipId => $shipArr)
			{
				$TargetAddedGoods .= ', ' . __('main.tech.'.$shipId) . ': ' . $shipArr['count'];
			}

			$TargetMessage = __('fleet_engine.sys_stay_mess_back') . $this->_fleet->getTargetAdressLink() . __('fleet_engine.sys_stay_mess_bend') . "<br />" . $TargetAddedGoods;

			User::sendMessage($this->_fleet->owner, 0, $this->_fleet->end_time, 5, __('fleet_engine.sys_mess_qg'), $TargetMessage);
		}
	}
}