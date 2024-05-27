<?php

namespace App\Missions;

use App\Models\Planet;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\FleetEngine;
use App\Models;
use App\Models\User;

class Destruction extends FleetEngine implements Mission
{
	public function targetEvent()
	{
		$mission = new Attack($this->fleet);
		$result = $mission->targetEvent();

		if ($result == true) {
			$checkFleet = Models\Fleet::query()->find($this->fleet->id, ['fleet_array', 'won']);

			if ($checkFleet && $checkFleet->won == 1) {
				$this->fleet->fleet_array = $checkFleet->fleet_array;
				$this->fleet->won = $checkFleet->won;

				unset($checkFleet);

				$ripsKilled = false;
				$moonDestroyed = false;

				$Rips = 0;

				$fleetData = $this->fleet->getShips();

				if (isset($fleetData[214])) {
					$Rips = $fleetData[214]['count'];
				}

				if ($Rips > 0) {
					$TargetMoon = Planet::query()
						->where('galaxy', $this->fleet->end_galaxy)
						->where('system', $this->fleet->end_system)
						->where('planet', $this->fleet->end_planet)
						->where('planet_type', 3)
						->first();

					$CurrentUser = DB::selectOne("SELECT rpg_admiral, rpg_ingenieur FROM users WHERE id = '" . $this->fleet->user_id . "'");

					$moonDestroyChance = round((100 - sqrt($TargetMoon->diameter)) * (sqrt($Rips)));

					if ($CurrentUser['rpg_admiral'] > time()) {
						$moonDestroyChance = $moonDestroyChance * 1.1;
					}

					$moonDestroyChance 	= max(min(floor($moonDestroyChance), 100), 0);
					$fleetDestroyChance = (sqrt($TargetMoon->diameter)) / 4;

					if ($Rips > 150) {
						$fleetDestroyChance *= 0.1;
					} elseif ($Rips > 100) {
						$fleetDestroyChance *= 0.25;
					} elseif ($Rips > 50) {
						$fleetDestroyChance *= 0.5;
					} elseif ($Rips > 25) {
						$fleetDestroyChance *= 0.75;
					}

					if ($CurrentUser['rpg_ingenieur'] > time()) {
						$fleetDestroyChance *= 0.5;
					}

					$fleetDestroyChance = max(min(ceil($fleetDestroyChance), 100), 0);

					$randChance = random_int(1, 100);

					if ($randChance <= $moonDestroyChance) {
						$moonDestroyed = true;

						$TargetMoon->destruyed = now()->addDay();
						$TargetMoon->save();

						$TargetMoon->user->update([
							'planet_current' => DB::raw('planet_id')
						]);

						Models\Fleet::query()
							->where('start_galaxy', $this->fleet->end_galaxy)
							->where('start_system', $this->fleet->end_system)
							->where('start_planet', $this->fleet->end_planet)
							->where('start_type', 3)
							->update(['start_type' => 1]);

						Models\Fleet::query()
							->where('end_galaxy', $this->fleet->end_galaxy)
							->where('end_system', $this->fleet->end_system)
							->where('end_planet', $this->fleet->end_planet)
							->where('start_type', 3)
							->update(['end_type' => 1]);

						Models\Queue::query()->where('planet_id', $TargetMoon->id)->delete();
					} else {
						$randChance = random_int(1, 100);

						if ($randChance <= $fleetDestroyChance) {
							$ripsKilled = true;

							$this->killFleet();

							$debree = $this->convertFleetToDebris($fleetData);

							if ($debree['metal'] > 0 && $debree['crystal'] > 0) {
								Models\Planet::query()->where('galaxy', $this->fleet->end_galaxy)
									->where('system', $this->fleet->end_system)
									->where('planet', $this->fleet->end_planet)
									->where('planet_type', '!=', 3)
									->update([
										'debris_metal' => DB::raw('debris_metal + ' . $debree['metal']),
										'debris_crystal' => DB::raw('debris_metal + ' . $debree['crystal']),
									]);
							}
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

					User::sendMessage($this->fleet->user_id, 0, $this->fleet->start_time, 4, __('fleet_engine.sys_mess_destruc_report'), $message);
					User::sendMessage($TargetMoon->user_id, 0, $this->fleet->start_time, 4, __('fleet_engine.sys_mess_destruc_report'), $message);

					Cache::forget('app::planetlist_' . $TargetMoon->user_id);
				} else {
					User::sendMessage($this->fleet->user_id, 0, $this->fleet->start_time, 4, __('fleet_engine.sys_mess_destruc_report'), __('fleet_engine.sys_destruc_stop'));
				}
			} else {
				User::sendMessage($this->fleet->user_id, 0, $this->fleet->start_time, 4, __('fleet_engine.sys_mess_destruc_report'), __('fleet_engine.sys_destruc_stop'));
			}
		}
	}

	public function endStayEvent()
	{
		return;
	}

	public function returnEvent()
	{
		$this->restoreFleetToPlanet();
		$this->killFleet();
	}
}
