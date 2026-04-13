<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Coordinates;
use App\Engine\Enums\MessageType;
use App\Engine\Messages\Types\MissionStayMessage;
use App\Engine\Messages\Types\MissionStayReturnMessage;
use App\Models\Planet;
use App\Notifications\SystemMessage;

class Stay extends BaseMission
{
	public static function isMissionPossible(Planet $planet, Coordinates $target, ?Planet $targetPlanet, array $units = [], bool $isAssault = false): bool
	{
		return $targetPlanet && ($targetPlanet->user_id == $planet->user_id || $targetPlanet->user->isAdmin());
	}

	public function targetEvent(): void
	{
		$targetPlanet = Planet::findByCoordinates($this->fleet->getDestinationCoordinates());

		if (!$targetPlanet || $targetPlanet->user_id != $this->fleet->target_user_id) {
			$this->return();
		} else {
			$this->restoreFleetToPlanet(false);
			$this->killFleet();

			$message = [
				...$this->fleet->getDestinationCoordinates()->toArray(),
				'metal' => $this->fleet->resource_metal,
				'crystal' => $this->fleet->resource_crystal,
				'deuterium' => $this->fleet->resource_deuterium,
				'units' => [],
			];

			foreach ($this->fleet->entities as $entity) {
				$message['units'][$entity->id] = $entity->count;
			}

			$this->fleet->target->notify(
				new SystemMessage(MessageType::Fleet, new MissionStayMessage($message))
			);
		}
	}

	public function returnEvent(): void
	{
		$targetPlanet = Planet::findByCoordinates($this->fleet->getOriginCoordinates());

		if (!$targetPlanet || $targetPlanet->user_id != $this->fleet->user_id) {
			$this->killFleet();
		} else {
			$this->restoreFleetToPlanet();
			$this->killFleet();

			$message = [
				...$this->fleet->getDestinationCoordinates()->toArray(),
				'metal' => $this->fleet->resource_metal,
				'crystal' => $this->fleet->resource_crystal,
				'deuterium' => $this->fleet->resource_deuterium,
				'units' => [],
			];

			foreach ($this->fleet->entities as $entity) {
				$message['units'][$entity->id] = $entity->count;
			}

			$this->fleet->user->notify(
				new SystemMessage(MessageType::Fleet, new MissionStayReturnMessage($message))
			);
		}
	}
}
