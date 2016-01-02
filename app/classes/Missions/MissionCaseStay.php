<?php

namespace App\Missions;

use App\Fleet;
use App\FleetEngine;
use App\Helpers;

class MissionCaseStay extends FleetEngine implements Mission
{
	function __construct($Fleet)
	{
		$this->_fleet = $Fleet;
	}

	public function TargetEvent()
	{
		$TargetPlanet = $this->db->query("SELECT id_owner FROM game_planets WHERE `galaxy` = '" . $this->_fleet['fleet_end_galaxy'] . "' AND `system` = '" . $this->_fleet['fleet_end_system'] . "' AND `planet` = '" . $this->_fleet['fleet_end_planet'] . "' AND `planet_type` = '" . $this->_fleet['fleet_end_type'] . "';")->fetch();

		if ($TargetPlanet['id_owner'] != $this->_fleet['fleet_target_owner'])
		{
			$this->ReturnFleet();
		}
		else
		{
			$this->RestoreFleetToPlanet(false);
			$this->KillFleet();

			$TargetAddedGoods = '';

			$fleetData = Fleet::unserializeFleet($this->_fleet['fleet_array']);

			foreach ($fleetData as $shipId => $shipArr)
			{
				$TargetAddedGoods .= ', ' . _getText('tech', $shipId) . ': ' . $shipArr['cnt'];
			}

			$TargetMessage = sprintf(_getText('sys_stat_mess'),
								Helpers::GetTargetAdressLink($this->_fleet),
								Helpers::pretty_number($this->_fleet['fleet_resource_metal']), _getText('Metal'),
								Helpers::pretty_number($this->_fleet['fleet_resource_crystal']), _getText('Crystal'),
								Helpers::pretty_number($this->_fleet['fleet_resource_deuterium']), _getText('Deuterium'));

			if ($TargetAddedGoods != '')
				$TargetMessage .= '<br>'.trim(substr($TargetAddedGoods, 1));

			$this->game->sendMessage($this->_fleet['fleet_target_owner'], 0, $this->_fleet['fleet_start_time'], 5, _getText('sys_mess_qg'), $TargetMessage);
		}
	}

	public function EndStayEvent()
	{
		return;
	}

	public function ReturnEvent()
	{
		$TargetPlanet = $this->db->query("SELECT id_owner FROM game_planets WHERE `galaxy` = '" . $this->_fleet['fleet_start_galaxy'] . "' AND `system` = '" . $this->_fleet['fleet_start_system'] . "' AND `planet` = '" . $this->_fleet['fleet_start_planet'] . "' AND `planet_type` = '" . $this->_fleet['fleet_start_type'] . "';")->fetch();

		if ($TargetPlanet['id_owner'] != $this->_fleet['fleet_owner'])
		{
			$this->KillFleet();
		}
		else
		{
			$this->RestoreFleetToPlanet();
			$this->KillFleet();

			$TargetAddedGoods = sprintf(_getText('sys_stay_mess_goods'), _getText('Metal'), Helpers::pretty_number($this->_fleet['fleet_resource_metal']), _getText('Crystal'), Helpers::pretty_number($this->_fleet['fleet_resource_crystal']), _getText('Deuterium'), Helpers::pretty_number($this->_fleet['fleet_resource_deuterium']));

			$fleetData = Fleet::unserializeFleet($this->_fleet['fleet_array']);

			foreach ($fleetData as $shipId => $shipArr)
			{
				$TargetAddedGoods .= ', ' . _getText('tech', $shipId) . ': ' . $shipArr['cnt'];
			}

			$TargetMessage = _getText('sys_stay_mess_back') . Helpers::GetTargetAdressLink($this->_fleet) . _getText('sys_stay_mess_bend') . "<br />" . $TargetAddedGoods;

			$this->game->sendMessage($this->_fleet['fleet_owner'], 0, $this->_fleet['fleet_end_time'], 5, _getText('sys_mess_qg'), $TargetMessage);
		}
	}
}

?>