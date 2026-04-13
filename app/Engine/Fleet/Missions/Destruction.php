<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Coordinates;
use App\Engine\Enums\FleetDirection;
use App\Engine\Enums\MessageType;
use App\Engine\Enums\PlanetType;
use App\Engine\Formulas;
use App\Engine\Messages\Types\MissionDestructionFailureMessage;
use App\Engine\Messages\Types\MissionDestructionMessage;
use App\Models;
use App\Models\Planet;
use App\Notifications\SystemMessage;
use App\Services\FleetService;
use Illuminate\Support\Facades\Cache;

class Destruction extends BaseMission
{
	public static function isMissionPossible(Planet $planet, Coordinates $target, ?Planet $targetPlanet, array $units = [], bool $isAssault = false): bool
	{
		return $target->getType() == PlanetType::MOON && !empty($units[214]) && $targetPlanet && $planet->user_id == $targetPlanet->user_id;
	}

	public function targetEvent(): void
	{
		$mission = new Attack($this->fleet);
		$mission->targetEvent();

		$checkFleet = Models\Fleet::findOne($this->fleet);

		if ($checkFleet && $checkFleet->mess == 1) {
			return;
		}

		if ($checkFleet && $checkFleet->won == 1) {
			$this->fleet->entities = $checkFleet->entities;
			$this->fleet->won = $checkFleet->won;

			unset($checkFleet);

			$ripsKilled = false;
			$moonDestroyed = false;

			$rips = 0;

			if ($ships = $this->fleet->entities->getByEntityId(214)) {
				$rips = $ships->count;
			}

			if ($rips > 0) {
				$targetMoon = Planet::findByCoordinates(
					new Coordinates($this->fleet->end_galaxy, $this->fleet->end_system, $this->fleet->end_planet, PlanetType::MOON)
				);

				$moonDestroyChance = Formulas::getMoonDestructionChance($targetMoon->diameter, $rips);

				if ($this->fleet->user->rpg_admiral?->isFuture()) {
					$moonDestroyChance *= 1.1;
				}

				$fleetDestroyChance = Formulas::getDeathStarsDestructionChance($targetMoon->diameter, $rips);

				if ($this->fleet->user->rpg_ingenieur?->isFuture()) {
					$fleetDestroyChance *= 0.5;
				}

				$moonDestroyChance 	= max(min((int) floor($moonDestroyChance), 100), 0);
				$fleetDestroyChance = max(min((int) ceil($fleetDestroyChance), 100), 0);

				if (random_int(1, 100) <= $moonDestroyChance) {
					$moonDestroyed = true;

					$targetMoon->destroyed_at = now()->addDay();
					$targetMoon->save();

					if ($targetMoon->user->planet_current == $targetMoon->id) {
						$targetMoon->user->update([
							'planet_current' => $targetMoon->user->planet_id,
						]);
					}

					$coordinates = $this->fleet->getDestinationCoordinates();
					$coordinates->setType(PlanetType::MOON);

					Models\Fleet::query()
						->coordinates(FleetDirection::START, $coordinates)
						->update(['start_type' => PlanetType::PLANET]);

					Models\Fleet::query()
						->coordinates(FleetDirection::END, $coordinates)
						->update(['end_type' => PlanetType::PLANET]);

					$targetMoon->queue()->delete();
				}

				if (random_int(1, 100) <= $fleetDestroyChance) {
					$ripsKilled = true;

					$this->killFleet();

					$debris = FleetService::convertFleetToDebris($this->fleet->entities);

					if ($debris['metal'] > 0 && $debris['crystal'] > 0) {
						Models\Planet::query()->coordinates($this->fleet->getDestinationCoordinates(false))
							->whereNot('planet_type', PlanetType::MOON)
							->incrementEach([
								'debris_metal' => $debris['metal'],
								'debris_crystal' => $debris['crystal'],
							]);
					}
				}

				$message = new MissionDestructionMessage([
					'destroyed' => $moonDestroyed,
					'killed' => $ripsKilled,
				]);

				$this->fleet->user->notify(new SystemMessage(MessageType::Battle, $message));
				$targetMoon->user->notify(new SystemMessage(MessageType::Battle, $message));

				Cache::forget('app::planetlist_' . $targetMoon->user_id);
			} else {
				$this->fleet->user->notify(
					new SystemMessage(MessageType::Battle, new MissionDestructionFailureMessage())
				);
			}
		} else {
			$this->fleet->user->notify(
				new SystemMessage(MessageType::Battle, new MissionDestructionFailureMessage())
			);
		}
	}
}
