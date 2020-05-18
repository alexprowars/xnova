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
	public function targetEvent()
	{
		$this->restoreFleetToPlanet(false, false);

		$Message = sprintf(
			__('fleet_engine.sys_tran_mess_owner'),
			$this->fleet->target_owner_name,
			$this->fleet->getTargetAdressLink(),
			$this->fleet->resource_metal,
			__('main.Metal'),
			$this->fleet->resource_crystal,
			__('main.Crystal'),
			$this->fleet->resource_deuterium,
			__('main.Deuterium')
		);

		User::sendMessage($this->fleet->owner, 0, $this->fleet->start_time, 5, __('fleet_engine.sys_mess_tower'), $Message);

		if ($this->fleet->target_owner != $this->fleet->owner) {
			$Message = sprintf(
				__('fleet_engine.sys_tran_mess_user'),
				$this->fleet->owner_name,
				$this->fleet->getStartAdressLink(),
				$this->fleet->target_owner_name,
				$this->fleet->getTargetAdressLink(),
				$this->fleet->resource_metal,
				__('main.Metal'),
				$this->fleet->resource_crystal,
				__('main.Crystal'),
				$this->fleet->resource_deuterium,
				__('main.Deuterium')
			);

			User::sendMessage($this->fleet->target_owner, 0, $this->fleet->start_time, 5, __('fleet_engine.sys_mess_tower'), $Message);
		}

		$this->returnFleet(['resource_metal' => 0, 'resource_crystal' => 0, 'resource_deuterium' => 0]);
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
