<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Coordinates;
use App\Engine\Enums\MessageType;
use App\Models\Planet;
use App\Notifications\MessageNotification;

class StayAlly extends BaseMission
{
	public function isMissionPossible(Planet $planet, Coordinates $target, ?Planet $targetPlanet, array $units = [], bool $isAssault = false): bool
	{
		return $targetPlanet && $targetPlanet->user_id != $planet->user_id && !(count($units) == 1 && !empty($units[210]));
	}

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
