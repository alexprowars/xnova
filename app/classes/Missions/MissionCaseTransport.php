<?php

namespace App\Missions;

use App\FleetEngine;
use App\Helpers;

class MissionCaseTransport extends FleetEngine implements Mission
{
	function __construct($Fleet)
	{
			$this->_fleet = $Fleet;
	}

	public function TargetEvent()
	{
		$this->RestoreFleetToPlanet(false, false);

		$Message = sprintf(_getText('sys_tran_mess_owner'),
					$this->_fleet['fleet_target_owner_name'], Helpers::GetTargetAdressLink($this->_fleet, ''),
					$this->_fleet['fleet_resource_metal'], _getText('Metal'),
					$this->_fleet['fleet_resource_crystal'], _getText('Crystal'),
					$this->_fleet['fleet_resource_deuterium'], _getText('Deuterium'));

		$this->game->sendMessage($this->_fleet['fleet_owner'], 0, $this->_fleet['fleet_start_time'], 5, _getText('sys_mess_tower'), $Message);

		if ($this->_fleet['fleet_target_owner'] != $this->_fleet['fleet_owner'])
		{
			$Message = sprintf(_getText('sys_tran_mess_user'),
						$this->_fleet['fleet_owner_name'], Helpers::GetStartAdressLink($this->_fleet, ''),
						$this->_fleet['fleet_target_owner_name'], Helpers::GetTargetAdressLink($this->_fleet, ''),
						$this->_fleet['fleet_resource_metal'], _getText('Metal'),
						$this->_fleet['fleet_resource_crystal'], _getText('Crystal'),
						$this->_fleet['fleet_resource_deuterium'], _getText('Deuterium'));

			$this->game->sendMessage($this->_fleet['fleet_target_owner'], 0, $this->_fleet['fleet_start_time'], 5, _getText('sys_mess_tower'), $Message);
		}

		$this->ReturnFleet(array('fleet_resource_metal' => 0, 'fleet_resource_crystal' => 0, 'fleet_resource_deuterium' => 0));
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

?>