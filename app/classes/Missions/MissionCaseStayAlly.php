<?php

namespace App\Missions;

use App\FleetEngine;
use App\Helpers;

class MissionCaseStayAlly extends FleetEngine implements Mission
{
	function __construct($Fleet)
	{
			$this->_fleet = $Fleet;
	}

	public function TargetEvent()
	{
		$this->StayFleet();

		$Message = sprintf(_getText('sys_stay_mess_user'), $this->_fleet['owner_name'], Helpers::GetStartAdressLink($this->_fleet, ''), $this->_fleet['target_owner_name'], Helpers::GetTargetAdressLink($this->_fleet, ''));

		$this->game->sendMessage($this->_fleet['owner'], 0, $this->_fleet['start_time'], 0, _getText('sys_mess_tower'), $Message);
	}

	public function EndStayEvent()
	{
		$this->ReturnFleet();
	}

	public function ReturnEvent()
	{
		$this->RestoreFleetToPlanet();
		$this->KillFleet();
	}
}

?>