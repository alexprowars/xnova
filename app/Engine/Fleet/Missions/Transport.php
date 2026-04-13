<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Coordinates;
use App\Engine\Enums\MessageType;
use App\Engine\Messages\Types\MissionTransportArrivedMessage;
use App\Engine\Messages\Types\MissionTransportReceivedMessage;
use App\Models\Planet;
use App\Notifications\SystemMessage;

class Transport extends BaseMission
{
	public static function isMissionPossible(Planet $planet, Coordinates $target, ?Planet $targetPlanet, array $units = [], bool $isAssault = false): bool
	{
		return $targetPlanet && (!empty($units[202]) || !empty($units[203]));
	}

	public function targetEvent(): void
	{
		$this->restoreFleetToPlanet(false, false);

		$message = new MissionTransportArrivedMessage([
			'target_name' => $this->fleet->target_user_name,
			'target' => $this->fleet->getDestinationCoordinates(false)->toArray(),
			'metal' => $this->fleet->resource_metal,
			'crystal' => $this->fleet->resource_crystal,
			'deuterium' => $this->fleet->resource_deuterium,
		]);

		$this->fleet->user->notify(new SystemMessage(MessageType::Fleet, $message));

		if ($this->fleet->target_user_id != $this->fleet->user_id) {
			$message = new MissionTransportReceivedMessage([
				'start_name' => $this->fleet->user_name,
				'start' => $this->fleet->getOriginCoordinates(false)->toArray(),
				'target_name' => $this->fleet->target_user_name,
				'target' => $this->fleet->getDestinationCoordinates(false)->toArray(),
				'metal' => $this->fleet->resource_metal,
				'crystal' => $this->fleet->resource_crystal,
				'deuterium' => $this->fleet->resource_deuterium,
			]);

			$this->fleet->target->notify(new SystemMessage(MessageType::Fleet, $message));
		}

		$this->fleet->resource_metal = 0;
		$this->fleet->resource_crystal = 0;
		$this->fleet->resource_deuterium = 0;

		$this->return();
	}
}
