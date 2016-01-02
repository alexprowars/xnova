<?php

namespace App\Missions;

use App\Fleet;
use App\FleetEngine;
use App\Models\Planet;

class MissionCaseColonisation extends FleetEngine implements Mission
{
	function __construct($Fleet)
	{
			$this->_fleet = $Fleet;
	}

	public function TargetEvent()
	{
		$MaxColo = $this->db->query("SELECT `colonisation_tech` FROM game_users WHERE id = ".$this->_fleet['fleet_owner']."")->fetch();
		$iMaxColo = $MaxColo['colonisation_tech'] + 1;

		if ($iMaxColo > $this->config->game->get('maxPlanets', 9))
			$iMaxColo = $this->config->game->get('maxPlanets', 9);

		$planet = new Planet();

		$iPlanetCount = $this->db->fetchColumn("SELECT count(*) as num FROM game_planets WHERE `id_owner` = '" . $this->_fleet['fleet_owner'] . "' AND `planet_type` = '1'");

		$TargetAdress = sprintf(_getText('sys_adress_planet'), $this->_fleet['fleet_end_galaxy'], $this->_fleet['fleet_end_system'], $this->_fleet['fleet_end_planet']);

		if ($planet->isPositionFree($this->_fleet['fleet_end_galaxy'], $this->_fleet['fleet_end_system'], $this->_fleet['fleet_end_planet']))
		{
			if ($iPlanetCount >= $iMaxColo)
			{
				$TheMessage = _getText('sys_colo_arrival') . $TargetAdress . _getText('sys_colo_maxcolo') . $iMaxColo . _getText('sys_colo_planet');

				$this->game->sendMessage($this->_fleet['fleet_owner'], 0, $this->_fleet['fleet_start_time'], 0, _getText('sys_colo_mess_from'), $TheMessage);

				$this->ReturnFleet();
			}
			else
			{
				$NewOwnerPlanet = $planet->createPlanet($this->_fleet['fleet_end_galaxy'], $this->_fleet['fleet_end_system'], $this->_fleet['fleet_end_planet'], $this->_fleet['fleet_owner'], _getText('sys_colo_defaultname'), false);

				if ($NewOwnerPlanet !== false)
				{
					$TheMessage = _getText('sys_colo_arrival') . $TargetAdress . _getText('sys_colo_allisok');

					$this->game->sendMessage($this->_fleet['fleet_owner'], 0, $this->_fleet['fleet_start_time'], 0, _getText('sys_colo_mess_from'), $TheMessage);

					$NewFleet = "";

					$fleetData = Fleet::unserializeFleet($this->_fleet['fleet_array']);

					foreach ($fleetData as $shipId => $shipArr)
					{
						if ($shipId == 208 && $shipArr['cnt'] > 0)
							$NewFleet .= $shipId . "," . ($shipArr['cnt'] - 1) . "!0;";
						elseif ($shipArr['cnt'] > 0)
							$NewFleet .= $shipId . "," . $shipArr['cnt'] . "!;";
					}

					$this->_fleet['fleet_array'] = $NewFleet;

					$this->RestoreFleetToPlanet(false);
					$this->KillFleet();

					$this->cache->delete('app::planetlist_'.$this->_fleet['fleet_owner']);
				}
				else
				{
					$this->ReturnFleet();

					$TheMessage = _getText('sys_colo_arrival') . $TargetAdress . _getText('sys_colo_badpos');

					$this->game->sendMessage($this->_fleet['fleet_owner'], 0, $this->_fleet['fleet_start_time'], 0, _getText('sys_colo_mess_from'), $TheMessage);
				}
			}
		}
		else
		{
			$this->ReturnFleet();

			$TheMessage = _getText('sys_colo_arrival') . $TargetAdress . _getText('sys_colo_notfree');

			$this->game->sendMessage($this->_fleet['fleet_owner'], 0, $this->_fleet['fleet_end_time'], 0, _getText('sys_colo_mess_from'), $TheMessage);
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