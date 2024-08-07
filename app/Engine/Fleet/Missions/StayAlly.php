<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Enums\MessageType;
use App\Notifications\MessageNotification;

class StayAlly extends BaseMission
{
	public function targetEvent()
	{
		$this->stayFleet();

		$Message = sprintf(__('fleet_engine.sys_stay_mess_user'), $this->fleet->user_name, $this->fleet->getStartAdressLink(), $this->fleet->target_user_name, $this->fleet->getTargetAdressLink());

		$this->fleet->user->notify(new MessageNotification(null, MessageType::Alliance, __('fleet_engine.sys_mess_tower'), $Message));
	}

	public function endStayEvent()
	{
		$this->return();
	}
}
