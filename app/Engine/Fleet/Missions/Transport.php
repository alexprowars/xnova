<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Coordinates;
use App\Engine\Enums\MessageType;
use App\Models\Planet;
use App\Notifications\MessageNotification;

class Transport extends BaseMission
{
	public function isMissionPossible(Planet $planet, Coordinates $target, ?Planet $targetPlanet, array $units = [], bool $isAssault = false): bool
	{
		return $targetPlanet && (!empty($units[202]) || !empty($units[203]));
	}

	public function targetEvent()
	{
		$this->restoreFleetToPlanet(false, false);

		$message = __('fleet_engine.sys_tran_mess_owner', [
			'user' => $this->fleet->target_user_name,
			'target' => $this->fleet->getTargetAdressLink(),
			'm' => $this->fleet->resource_metal,
			'mt' => __('main.metal'),
			'c' => $this->fleet->resource_crystal,
			'ct' => __('main.crystal'),
			'd' => $this->fleet->resource_deuterium,
			'dt' => __('main.deuterium')
		]);

		$this->fleet->user->notify(new MessageNotification(null, MessageType::Fleet, __('fleet_engine.sys_mess_tower'), $message));

		if ($this->fleet->target_user_id != $this->fleet->user_id) {
			$message = __('fleet_engine.sys_tran_mess_user', [
				'user' => $this->fleet->user_name,
				'start' => $this->fleet->getStartAdressLink(),
				'target_user' => $this->fleet->target_user_name,
				'target' => $this->fleet->getTargetAdressLink(),
				'm' => $this->fleet->resource_metal,
				'mt' => __('main.metal'),
				'c' => $this->fleet->resource_crystal,
				'ct' => __('main.crystal'),
				'd' => $this->fleet->resource_deuterium,
				'dt' => __('main.deuterium')
			]);

			$this->fleet->target->notify(new MessageNotification(null, MessageType::Fleet, __('fleet_engine.sys_mess_tower'), $message));
		}

		$this->fleet->resource_metal = 0;
		$this->fleet->resource_crystal = 0;
		$this->fleet->resource_deuterium = 0;

		$this->return();
	}
}
