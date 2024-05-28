<?php

namespace App\Missions;

use App\FleetEngine;
use App\Models\User;

class StayAlly extends FleetEngine implements Mission
{
	public function targetEvent()
	{
		$this->stayFleet();

		$Message = sprintf(__('fleet_engine.sys_stay_mess_user'), $this->fleet->user_name, $this->fleet->getStartAdressLink(), $this->fleet->target_user_name, $this->fleet->getTargetAdressLink());

		User::sendMessage($this->fleet->user_id, 0, $this->fleet->start_time, 1, __('fleet_engine.sys_mess_tower'), $Message);
	}

	public function endStayEvent()
	{
		$this->fleet->return();
	}

	public function returnEvent()
	{
		$this->restoreFleetToPlanet();
		$this->killFleet();
	}
}