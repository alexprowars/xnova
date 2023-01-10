<?php

namespace App\Missions;

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\FleetEngine;
use App\Models;
use App\User;

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
					$TargetMoon = DB::selectOne("SELECT id, id_owner, diameter FROM planets WHERE galaxy = '" . $this->fleet->end_galaxy . "' AND system = '" . $this->fleet->end_system . "' AND planet = '" . $this->fleet->end_planet . "' AND planet_type = '3'");
					$CurrentUser = DB::selectOne("SELECT rpg_admiral, rpg_ingenieur FROM users WHERE id = '" . $this->fleet->owner . "'");

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

					$randChance = mt_rand(1, 100);

					if ($randChance <= $moonDestroyChance) {
						$moonDestroyed = true;

						DB::statement("UPDATE planets SET destruyed = " . (time() + 60 * 60 * 24) . ", id_owner = 0 WHERE id = '" . $TargetMoon->id . "'");
						DB::statement("UPDATE users SET planet_current = planet_id WHERE id = " . $TargetMoon->id_owner . "");

						DB::statement("UPDATE fleets SET start_type = 1 WHERE start_galaxy = " . $this->fleet->end_galaxy . " AND start_system = " . $this->fleet->end_system . " AND start_planet = " . $this->fleet->end_planet . " AND start_type = 3");
						DB::statement("UPDATE fleets SET end_type = 1 WHERE end_galaxy = " . $this->fleet->end_galaxy . " AND end_system = " . $this->fleet->end_system . " AND end_planet = " . $this->fleet->end_planet . " AND end_type = 3");

						Models\Queue::query()->where('planet_id', $TargetMoon->id)->delete();
					} else {
						$randChance = mt_rand(1, 100);

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

					User::sendMessage($this->fleet->owner, 0, $this->fleet->start_time, 3, __('fleet_engine.sys_mess_destruc_report'), $message);
					User::sendMessage($TargetMoon->id_owner, 0, $this->fleet->start_time, 3, __('fleet_engine.sys_mess_destruc_report'), $message);

					Cache::forget('app::planetlist_' . $TargetMoon->id_owner);
				} else {
					User::sendMessage($this->fleet->owner, 0, $this->fleet->start_time, 3, __('fleet_engine.sys_mess_destruc_report'), __('fleet_engine.sys_destruc_stop'));
				}
			} else {
				User::sendMessage($this->fleet->owner, 0, $this->fleet->start_time, 3, __('fleet_engine.sys_mess_destruc_report'), __('fleet_engine.sys_destruc_stop'));
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
