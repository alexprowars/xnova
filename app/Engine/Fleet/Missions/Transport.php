<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Enums\MessageType;
use App\Notifications\MessageNotification;

class Transport extends BaseMission
{
	public function targetEvent()
	{
		$this->restoreFleetToPlanet(false, false);

		$message = sprintf(
			__('fleet_engine.sys_tran_mess_owner'),
			$this->fleet->target_user_name,
			$this->fleet->getTargetAdressLink(),
			$this->fleet->resource_metal,
			__('main.Metal'),
			$this->fleet->resource_crystal,
			__('main.Crystal'),
			$this->fleet->resource_deuterium,
			__('main.Deuterium')
		);

		$this->fleet->user->notify(new MessageNotification(null, MessageType::Fleet, __('fleet_engine.sys_mess_tower'), $message));

		if ($this->fleet->target_user_id != $this->fleet->user_id) {
			$message = sprintf(
				__('fleet_engine.sys_tran_mess_user'),
				$this->fleet->user_name,
				$this->fleet->getStartAdressLink(),
				$this->fleet->target_user_name,
				$this->fleet->getTargetAdressLink(),
				$this->fleet->resource_metal,
				__('main.Metal'),
				$this->fleet->resource_crystal,
				__('main.Crystal'),
				$this->fleet->resource_deuterium,
				__('main.Deuterium')
			);

			$this->fleet->target->notify(new MessageNotification(null, MessageType::Fleet, __('fleet_engine.sys_mess_tower'), $message));
		}

		$this->return(['resource_metal' => 0, 'resource_crystal' => 0, 'resource_deuterium' => 0]);
	}
}
