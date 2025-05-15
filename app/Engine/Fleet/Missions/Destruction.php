<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Coordinates;
use App\Engine\Enums\FleetDirection;
use App\Engine\Enums\MessageType;
use App\Engine\Enums\PlanetType;
use App\Models;
use App\Models\Planet;
use App\Models\User;
use App\Notifications\MessageNotification;
use Illuminate\Support\Facades\Cache;

class Destruction extends BaseMission
{
	public function isMissionPossible(Planet $planet, Coordinates $target, ?Planet $targetPlanet, array $units = [], bool $isAssault = false): bool
	{
		return $target->getType() == PlanetType::MOON && !empty($units[214]) && $targetPlanet && $planet->user_id == $targetPlanet->user_id;
	}

	public function targetEvent()
	{
		$mission = new Attack($this->fleet);

		if (!$mission->targetEvent()) {
			return;
		}

		$checkFleet = Models\Fleet::find($this->fleet->id, ['fleet_array', 'won']);

		if ($checkFleet && $checkFleet->won == 1) {
			$this->fleet->fleet_array = $checkFleet->fleet_array;
			$this->fleet->won = $checkFleet->won;

			unset($checkFleet);

			$ripsKilled = false;
			$moonDestroyed = false;

			$rips = 0;

			$fleetData = $this->fleet->getShips();

			if (isset($fleetData[214])) {
				$rips = $fleetData[214]['count'];
			}

			if ($rips > 0) {
				$targetMoon = Planet::findByCoordinates(
					new Coordinates($this->fleet->end_galaxy, $this->fleet->end_system, $this->fleet->end_planet, PlanetType::MOON)
				);

				$CurrentUser = User::find($this->fleet->user_id);

				$moonDestroyChance = round((100 - sqrt($targetMoon->diameter)) * sqrt($rips));

				if ($CurrentUser->rpg_admiral?->isFuture()) {
					$moonDestroyChance *= 1.1;
				}

				$moonDestroyChance 	= max(min(floor($moonDestroyChance), 100), 0);
				$fleetDestroyChance = sqrt($targetMoon->diameter) / 4;

				if ($rips > 150) {
					$fleetDestroyChance *= 0.1;
				} elseif ($rips > 100) {
					$fleetDestroyChance *= 0.25;
				} elseif ($rips > 50) {
					$fleetDestroyChance *= 0.5;
				} elseif ($rips > 25) {
					$fleetDestroyChance *= 0.75;
				}

				if ($CurrentUser->rpg_ingenieur?->isFuture()) {
					$fleetDestroyChance *= 0.5;
				}

				$fleetDestroyChance = max(min(ceil($fleetDestroyChance), 100), 0);

				if (random_int(1, 100) <= $moonDestroyChance) {
					$moonDestroyed = true;

					$targetMoon->destruyed_at = now()->addDay();
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
				} elseif (random_int(1, 100) <= $fleetDestroyChance) {
					$ripsKilled = true;

					$this->killFleet();

					$debree = $this->convertFleetToDebris($fleetData);

					if ($debree['metal'] > 0 && $debree['crystal'] > 0) {
						Models\Planet::coordinates($this->fleet->getDestinationCoordinates(false))
							->whereNot('planet_type', PlanetType::MOON)
							->incrementEach([
								'debris_metal' => $debree['metal'],
								'debris_crystal' => $debree['crystal'],
							]);
					}
				}

				$message = __('fleet_engine.sys_destruc_mess1');

				if ($moonDestroyed && !$ripsKilled) {
					$message .= __('fleet_engine.sys_destruc_reussi');
				}

				if ($moonDestroyed && $ripsKilled) {
					$message .= __('fleet_engine.sys_destruc_all');
				}

				if (!$moonDestroyed && $ripsKilled) {
					$message .= __('fleet_engine.sys_destruc_echec');
				}

				if (!$moonDestroyed && !$ripsKilled) {
					$message .= __('fleet_engine.sys_destruc_null');
				}

				$message .= "<br><br>" . __('fleet_engine.sys_destruc_lune') . $moonDestroyChance . "%. <br>" . __('fleet_engine.sys_destruc_rip') . $fleetDestroyChance . "%";

				$this->fleet->user->notify(new MessageNotification(null, MessageType::Battle, __('fleet_engine.sys_mess_destruc_report'), $message));
				$targetMoon->user->notify(new MessageNotification(null, MessageType::Battle, __('fleet_engine.sys_mess_destruc_report'), $message));

				Cache::forget('app::planetlist_' . $targetMoon->user_id);
			} else {
				$this->fleet->user->notify(new MessageNotification(null, MessageType::Battle, __('fleet_engine.sys_mess_destruc_report'), __('fleet_engine.sys_destruc_stop')));
			}
		} else {
			$this->fleet->user->notify(new MessageNotification(null, MessageType::Battle, __('fleet_engine.sys_mess_destruc_report'), __('fleet_engine.sys_destruc_stop')));
		}
	}
}
