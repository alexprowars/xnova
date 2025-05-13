<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Enums\MessageType;
use App\Notifications\MessageNotification;

class StayAlly extends BaseMission
{
	public function targetEvent()
	{
		$this->stayFleet();

		$message = __('fleet_engine.sys_stay_mess_user', [
			'user' => $this->fleet->user_name,
			'start' => $this->fleet->getStartAdressLink(),
			'target_user' => $this->fleet->target_user_name,
			'target' => $this->fleet->getTargetAdressLink(),
		]);

		$this->fleet->user->notify(new MessageNotification(null, MessageType::Alliance, __('fleet_engine.sys_mess_tower'), $message));
	}

	public function endStayEvent()
	{
		$this->return();
	}
}
