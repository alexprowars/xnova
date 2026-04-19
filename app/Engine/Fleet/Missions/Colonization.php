<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Coordinates;
use App\Engine\Enums\MessageType;
use App\Engine\Enums\PlanetType;
use App\Engine\Messages\AbstractMessage;
use App\Engine\Messages\Types\MissionColonizationErrorMessage;
use App\Engine\Messages\Types\MissionColonizationExistMessage;
use App\Engine\Messages\Types\MissionColonizationMaxReachedMessage;
use App\Engine\Messages\Types\MissionColonizationMessage;
use App\Facades\Galaxy;
use App\Models;
use App\Models\Planet;
use App\Notifications\SystemMessage;

class Colonization extends BaseMission
{
	public static function isMissionPossible(Planet $planet, Coordinates $target, ?Planet $targetPlanet, array $units = [], bool $isAssault = false): bool
	{
		return !empty($units[208]) && $targetPlanet;
	}

	public function targetEvent(): void
	{
		$maxPlanets = $this->fleet->user->getTechLevel('colonization') + 1;

		if ($maxPlanets > config('game.maxPlanets', 9)) {
			$maxPlanets = config('game.maxPlanets', 9);
		}

		if (Galaxy::isPositionFree($this->fleet->getDestinationCoordinates())) {
			$iPlanetCount = Models\Planet::query()
				->whereBelongsTo($this->fleet->user)
				->where('planet_type', PlanetType::PLANET)
				->count();

			if ($iPlanetCount >= $maxPlanets) {
				$this->sendNotify(new MissionColonizationMaxReachedMessage([
					'target' => $this->fleet->getDestinationCoordinates()->toArray(),
					'max' => $maxPlanets,
				]));

				$this->return();
			} else {
				$newOwnerPlanet = Galaxy::createPlanet(
					$this->fleet->getDestinationCoordinates(),
					$this->fleet->user,
					__('fleet_engine.sys_colo_defaultname')
				);

				if ($newOwnerPlanet) {
					$this->sendNotify(new MissionColonizationMessage([
						'target' => $this->fleet->getDestinationCoordinates()->toArray(),
					]));

					foreach ($this->fleet->entities as $entity) {
						if ($entity->id == 208 && $entity->count > 0) {
							$entity->count--;
						}
					}

					$this->restoreFleetToPlanet(false);
					$this->killFleet();
				} else {
					$this->return();

					$this->sendNotify(new MissionColonizationErrorMessage([
						'target' => $this->fleet->getDestinationCoordinates()->toArray(),
					]));
				}
			}
		} else {
			$this->return();

			$this->sendNotify(new MissionColonizationExistMessage([
				'target' => $this->fleet->getDestinationCoordinates()->toArray(),
			]));
		}
	}

	protected function sendNotify(AbstractMessage $message): void
	{
		$this->fleet->user->notify(new SystemMessage(MessageType::Fleet, $message));
	}
}
