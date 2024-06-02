<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Enums\MessageType;
use App\Engine\FleetEngine;
use App\Models\User;

class StayAlly extends FleetEngine implements Mission
{
	public function targetEvent()
	{
		$this->stayFleet();

		$Message = sprintf(__('fleet_engine.sys_stay_mess_user'), $this->fleet->user_name, $this->fleet->getStartAdressLink(), $this->fleet->target_user_name, $this->fleet->getTargetAdressLink());

		User::sendMessage($this->fleet->user_id, null, $this->fleet->start_time, MessageType::Alliance, __('fleet_engine.sys_mess_tower'), $Message);
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