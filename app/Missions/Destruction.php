<?php

namespace Xnova\Missions;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\FleetEngine;
use Xnova\Models\Fleet;
use Xnova\User;

class Destruction extends FleetEngine implements Mission
{
	public function TargetEvent()
	{
		$mission = new Attack($this->_fleet);
		$result = $mission->TargetEvent();

		if ($result == true)
		{
			$checkFleet = Fleet::findFirst(['columns' => 'fleet_array, won', 'conditions' => 'id = ?0', 'bind' => [$this->_fleet->id]]);

			if ($checkFleet && $checkFleet->won == 1)
			{
				$this->_fleet->fleet_array = $checkFleet->fleet_array;
				$this->_fleet->won = $checkFleet->won;

				unset($checkFleet);

				$ripsKilled = false;
				$moonDestroyed = false;

				$Rips = 0;

				$fleetData = $this->_fleet->getShips();

				if (isset($fleetData[214]))
					$Rips = $fleetData[214]['count'];

				if ($Rips > 0)
				{
					$TargetMoon = $this->db->query("SELECT id, id_owner, diameter FROM planets WHERE galaxy = '" . $this->_fleet->end_galaxy . "' AND system = '" . $this->_fleet->end_system . "' AND planet = '" . $this->_fleet->end_planet . "' AND planet_type = '3';")->fetch();
					$CurrentUser = $this->db->query("SELECT rpg_admiral, rpg_ingenieur FROM users WHERE id = '" . $this->_fleet->owner . "';")->fetch();

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

						$this->db->query("UPDATE planets SET destruyed = " . (time() + 60 * 60 * 24) . ", id_owner = 0 WHERE id = '" . $TargetMoon['id'] . "';");
						$this->db->query("UPDATE users SET planet_current = planet_id WHERE id = " . $TargetMoon['id_owner'] . ";");

						$this->db->query("UPDATE ".$this->_fleet->getSource()." SET start_type = 1 WHERE start_galaxy = " . $this->_fleet->end_galaxy . " AND start_system = " . $this->_fleet->end_system . " AND start_planet = " . $this->_fleet->end_planet . " AND start_type = 3;");
						$this->db->query("UPDATE ".$this->_fleet->getSource()." SET end_type = 1 WHERE end_galaxy = " . $this->_fleet->end_galaxy . " AND end_system = " . $this->_fleet->end_system . " AND end_planet = " . $this->_fleet->end_planet . " AND end_type = 3;");

						$queue = \Xnova\Models\Queue::find([
							'conditions' => 'planet_id = :planet:',
							'bind' => [
								'planet' => $TargetMoon['id']
							]
						]);

						foreach ($queue as $item)
							$item->delete();
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
								$this->db->updateAsDict('planets',
								[
									'+debris_metal' 	=> $debree['metal'],
									'+debris_crystal' 	=> $debree['crystal']
								],
								"galaxy = ".$this->_fleet->end_galaxy." AND system = ".$this->_fleet->end_system." AND planet = ".$this->_fleet->end_planet." AND planet_type != 3");
							}
						}
					}

					$message = __('fleet_engine.sys_destruc_mess1');

					if ($moonDestroyed && !$ripsKilled)
						$message .= __('fleet_engine.sys_destruc_reussi');

					if ($moonDestroyed && $ripsKilled)
						$message .= __('fleet_engine.sys_destruc_all');

					if (!$moonDestroyed && $ripsKilled)
						$message .= __('fleet_engine.sys_destruc_echec');

					if (!$moonDestroyed && !$ripsKilled)
						$message .= __('fleet_engine.sys_destruc_null');

					$message .= "<br><br>" . __('fleet_engine.sys_destruc_lune') . $moonDestroyChance . "%. <br>" . __('fleet_engine.sys_destruc_rip') . $fleetDestroyChance . "%";

					User::sendMessage($this->_fleet->owner, 0, $this->_fleet->start_time, 3, __('fleet_engine.sys_mess_destruc_report'), $message);
					User::sendMessage($TargetMoon['id_owner'], 0, $this->_fleet->start_time, 3, __('fleet_engine.sys_mess_destruc_report'), $message);

					$this->cache->delete('app::planetlist_'.$TargetMoon['id_owner']);
				}
				else
					User::sendMessage($this->_fleet->owner, 0, $this->_fleet->start_time, 3, __('fleet_engine.sys_mess_destruc_report'), __('fleet_engine.sys_destruc_stop'));
			}
			else
				User::sendMessage($this->_fleet->owner, 0, $this->_fleet->start_time, 3, __('fleet_engine.sys_mess_destruc_report'), __('fleet_engine.sys_destruc_stop'));
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