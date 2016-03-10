<?php

namespace App\Missions;

use App\Fleet;
use App\FleetEngine;

class MissionCaseDestruction extends FleetEngine implements Mission
{
	function __construct($Fleet)
	{
			$this->_fleet = $Fleet;
	}

	public function TargetEvent()
	{
		// Проводим бой
		$mission = new MissionCaseAttack($this->_fleet);
		$result = $mission->TargetEvent();

		if ($result == true)
		{
			$checkFleet = $this->db->query("SELECT fleet_array, won FROM game_fleets WHERE fleet_id = " . $this->_fleet['fleet_id'] . ";")->fetch();

			if (isset($checkFleet['fleet_array']) && $checkFleet['won'] == 1)
			{
				$this->_fleet['fleet_array'] = $checkFleet['fleet_array'];
				$this->_fleet['won'] = $checkFleet['won'];

				$ripsKilled = false;
				$moonDestroyed = false;

				$Rips = 0;

				$fleetData = Fleet::unserializeFleet($this->_fleet['fleet_array']);

				if (isset($fleetData[214]))
					$Rips = $fleetData[214]['cnt'];

				if ($Rips > 0)
				{
					$TargetMoon = $this->db->query("SELECT id, id_owner, diameter FROM game_planets WHERE `galaxy` = '" . $this->_fleet['fleet_end_galaxy'] . "' AND `system` = '" . $this->_fleet['fleet_end_system'] . "' AND `planet` = '" . $this->_fleet['fleet_end_planet'] . "' AND `planet_type` = '3';")->fetch();
					$CurrentUser = $this->db->query("SELECT `rpg_admiral`, `rpg_ingenieur` FROM game_users WHERE `id` = '" . $this->_fleet['fleet_owner'] . "';")->fetch();

					$moonDestroyChance = round((100 - sqrt($TargetMoon['diameter'])) * (sqrt($Rips)));

					if ($CurrentUser['rpg_admiral'] > time())
						$moonDestroyChance = $moonDestroyChance * 1.1;

					$moonDestroyChance 	= max(min(floor($moonDestroyChance), 100), 0);
					$fleetDestroyChance = (sqrt($TargetMoon['diameter'])) / 4;
					
					if ($Rips > 150)
						$fleetDestroyChance *= 0.1;
					elseif ($Rips > 100)
						$fleetDestroyChance *= 0.25;
					elseif ($Rips > 50)
						$fleetDestroyChance *= 0.5;
					elseif ($Rips > 25)
						$fleetDestroyChance *= 0.75;

					if ($CurrentUser['rpg_ingenieur'] > time())
						$fleetDestroyChance *= 0.5;

					$fleetDestroyChance = max(min(ceil($fleetDestroyChance), 100), 0);

					$randChance = mt_rand(1, 100);

					if ($randChance <= $moonDestroyChance)
					{
						$moonDestroyed = true;

						$this->db->query("UPDATE game_planets SET destruyed = " . (time() + 60 * 60 * 24) . ", id_owner = 0 WHERE `id` = '" . $TargetMoon['id'] . "';");
						$this->db->query("UPDATE game_users SET planet_current = planet_id WHERE id = " . $TargetMoon['id_owner'] . ";");

						$this->db->query("UPDATE game_fleets SET fleet_start_type = 1 WHERE fleet_start_galaxy = " . $this->_fleet['fleet_end_galaxy'] . " AND fleet_start_system = " . $this->_fleet['fleet_end_system'] . " AND fleet_start_planet = " . $this->_fleet['fleet_end_planet'] . " AND fleet_start_type = 3;");
						$this->db->query("UPDATE game_fleets SET fleet_end_type = 1 WHERE fleet_end_galaxy = " . $this->_fleet['fleet_end_galaxy'] . " AND fleet_end_system = " . $this->_fleet['fleet_end_system'] . " AND fleet_end_planet = " . $this->_fleet['fleet_end_planet'] . " AND fleet_end_type = 3;");
					}
					else
					{
						$randChance = mt_rand(1, 100);

						if ($randChance <= $fleetDestroyChance)
						{
							$ripsKilled = true;

							$this->KillFleet();

							$debree = $this->convertFleetToDebris($fleetData);

							if ($debree['metal'] > 0 && $debree['crystal'] > 0)
							{
								$this->db->updateAsDict('game_planets',
								[
									'+debris_metal' 	=> $debree['metal'],
									'+debris_crystal' 	=> $debree['crystal']
								],
								"galaxy = ".$this->_fleet['fleet_end_galaxy']." AND system = ".$this->_fleet['fleet_end_system']." AND planet = ".$this->_fleet['fleet_end_planet']." AND planet_type != 3");
							}
						}
					}

					$message = _getText('sys_destruc_mess1');

					if ($moonDestroyed && !$ripsKilled)
						$message .= _getText('sys_destruc_reussi');

					if ($moonDestroyed && $ripsKilled)
						$message .= _getText('sys_destruc_all');

					if (!$moonDestroyed && $ripsKilled)
						$message .= _getText('sys_destruc_echec');

					if (!$moonDestroyed && !$ripsKilled)
						$message .= _getText('sys_destruc_null');

					$message .= "<br><br>" . _getText('sys_destruc_lune') . $moonDestroyChance . "%. <br>" . _getText('sys_destruc_rip') . $fleetDestroyChance . "%";

					$this->game->sendMessage($this->_fleet['fleet_owner'], 0, $this->_fleet['fleet_start_time'], 3, _getText('sys_mess_destruc_report'), $message);
					$this->game->sendMessage($TargetMoon['id_owner'], 0, $this->_fleet['fleet_start_time'], 3, _getText('sys_mess_destruc_report'), $message);

					$this->cache->delete('app::planetlist_'.$TargetMoon['id_owner']);
				}
				else
					$this->game->sendMessage($this->_fleet['fleet_owner'], 0, $this->_fleet['fleet_start_time'], 3, _getText('sys_mess_destruc_report'), _getText('sys_destruc_stop'));
			}
			else
				$this->game->sendMessage($this->_fleet['fleet_owner'], 0, $this->_fleet['fleet_start_time'], 3, _getText('sys_mess_destruc_report'), _getText('sys_destruc_stop'));
		}
	}

	public function EndStayEvent()
	{
		return;
	}

	public function ReturnEvent()
	{
		$this->RestoreFleetToPlanet();
		$this->KillFleet();
	}
}

?>