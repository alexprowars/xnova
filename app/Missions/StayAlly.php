<?php

namespace Xnova\Missions;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\FleetEngine;
use Xnova\User;

class StayAlly extends FleetEngine implements Mission
{
	public function TargetEvent()
	{
		$this->StayFleet();

		$Message = sprintf(__('fleet_engine.sys_stay_mess_user'), $this->_fleet->owner_name, $this->_fleet->getStartAdressLink(), $this->_fleet->target_owner_name, $this->_fleet->getTargetAdressLink());

		User::sendMessage($this->_fleet->owner, 0, $this->_fleet->start_time, 0, __('fleet_engine.sys_mess_tower'), $Message);
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