<?php

namespace Xnova\Missions;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\FleetEngine;
use Xnova\User;

class MissionCaseStayAlly extends FleetEngine implements Mission
{
	public function TargetEvent()
	{
		$this->StayFleet();

		$Message = sprintf(_getText('sys_stay_mess_user'), $this->_fleet->owner_name, $this->_fleet->getStartAdressLink(), $this->_fleet->target_owner_name, $this->_fleet->getTargetAdressLink());

		User::sendMessage($this->_fleet->owner, 0, $this->_fleet->start_time, 0, _getText('sys_mess_tower'), $Message);
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