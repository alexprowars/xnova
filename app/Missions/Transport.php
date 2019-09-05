<?php

namespace Xnova\Missions;

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\FleetEngine;
use Xnova\User;

class Transport extends FleetEngine implements Mission
{
	public function TargetEvent()
	{
		$this->RestoreFleetToPlanet(false, false);

		$Message = sprintf(__('fleet_engine.sys_tran_mess_owner'),
					$this->_fleet->target_owner_name, $this->_fleet->getTargetAdressLink(),
					$this->_fleet->resource_metal, __('main.Metal'),
					$this->_fleet->resource_crystal, __('main.Crystal'),
					$this->_fleet->resource_deuterium, __('main.Deuterium'));

		User::sendMessage($this->_fleet->owner, 0, $this->_fleet->start_time, 5, __('fleet_engine.sys_mess_tower'), $Message);

		if ($this->_fleet->target_owner != $this->_fleet->owner)
		{
			$Message = sprintf(__('fleet_engine.sys_tran_mess_user'),
						$this->_fleet->owner_name, $this->_fleet->getStartAdressLink(),
						$this->_fleet->target_owner_name, $this->_fleet->getTargetAdressLink(),
						$this->_fleet->resource_metal, __('main.Metal'),
						$this->_fleet->resource_crystal, __('main.Crystal'),
						$this->_fleet->resource_deuterium, __('main.Deuterium'));

			User::sendMessage($this->_fleet->target_owner, 0, $this->_fleet->start_time, 5, __('fleet_engine.sys_mess_tower'), $Message);
		}

		$this->ReturnFleet(['resource_metal' => 0, 'resource_crystal' => 0, 'resource_deuterium' => 0]);
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