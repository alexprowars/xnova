<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Coordinates;
use App\Engine\Enums\MessageType;
use App\Engine\Messages\Types\AcsFleetArrivedMessage;
use App\Models\Planet;
use App\Notifications\SystemMessage;

class StayAlly extends BaseMission
{
	public static function isMissionPossible(Planet $planet, Coordinates $target, ?Planet $targetPlanet, array $units = [], bool $isAssault = false): bool
	{
		return $targetPlanet && $targetPlanet->user_id != $planet->user_id && !(count($units) == 1 && !empty($units[210]));
	}

	public function targetEvent(): void
	{
		$this->stayFleet();

		$message = new AcsFleetArrivedMessage([
			'start_name' => $this->fleet->user_name,
			'start' => $this->fleet->getOriginCoordinates(false)->toArray(),
			'target_user' => $this->fleet->target_user_name,
			'target' => $this->fleet->getDestinationCoordinates(false)->toArray(),
		]);

		$this->fleet->user->notify(new SystemMessage(MessageType::Alliance, $message));
	}

	public function endStayEvent(): void
	{
		$this->return();
	}
}
