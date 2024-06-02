<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Coordinates;
use App\Engine\Enums\MessageType;
use App\Engine\Enums\PlanetType;
use App\Engine\Fleet\FleetEngine;
use App\Models;
use App\Models\Planet;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Destruction extends FleetEngine implements Mission
{
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

					$targetMoon->destruyed = now()->addDay();
					$targetMoon->save();

					if ($targetMoon->user->planet_current == $targetMoon->id) {
						$targetMoon->user->update([
							'planet_current' => DB::raw('planet_id')
						]);
					}

					Models\Fleet::query()
						->where('start_galaxy', $this->fleet->end_galaxy)
						->where('start_system', $this->fleet->end_system)
						->where('start_planet', $this->fleet->end_planet)
						->where('start_type', PlanetType::MOON)
						->update(['start_type' => PlanetType::PLANET]);

					Models\Fleet::query()
						->where('end_galaxy', $this->fleet->end_galaxy)
						->where('end_system', $this->fleet->end_system)
						->where('end_planet', $this->fleet->end_planet)
						->where('start_type', PlanetType::MOON)
						->update(['end_type' => PlanetType::PLANET]);

					Models\Queue::query()->where('planet_id', $targetMoon->id)->delete();
				} elseif (random_int(1, 100) <= $fleetDestroyChance) {
					$ripsKilled = true;

					$this->killFleet();

					$debree = $this->convertFleetToDebris($fleetData);

					if ($debree['metal'] > 0 && $debree['crystal'] > 0) {
						Models\Planet::coordinates($this->fleet->getDestinationCoordinates(false))
							->where('planet_type', '!=', PlanetType::MOON)
							->update([
								'debris_metal' => DB::raw('debris_metal + ' . $debree['metal']),
								'debris_crystal' => DB::raw('debris_metal + ' . $debree['crystal']),
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

				User::sendMessage($this->fleet->user_id, null, $this->fleet->start_time, MessageType::Battle, __('fleet_engine.sys_mess_destruc_report'), $message);
				User::sendMessage($targetMoon->user_id, null, $this->fleet->start_time, MessageType::Battle, __('fleet_engine.sys_mess_destruc_report'), $message);

				Cache::forget('app::planetlist_' . $targetMoon->user_id);
			} else {
				User::sendMessage($this->fleet->user_id, null, $this->fleet->start_time, MessageType::Battle, __('fleet_engine.sys_mess_destruc_report'), __('fleet_engine.sys_destruc_stop'));
			}
		} else {
			User::sendMessage($this->fleet->user_id, null, $this->fleet->start_time, MessageType::Battle, __('fleet_engine.sys_mess_destruc_report'), __('fleet_engine.sys_destruc_stop'));
		}
	}

	public function endStayEvent()
	{
	}

	public function returnEvent()
	{
		$this->restoreFleetToPlanet();
		$this->killFleet();
	}
}
