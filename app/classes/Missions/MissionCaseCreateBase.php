<?php

namespace App\Missions;

use App\Fleet;
use App\FleetEngine;
use App\Models\Planet;

class MissionCaseCreateBase extends FleetEngine implements Mission
{
	function __construct($Fleet)
	{
			$this->_fleet = $Fleet;
	}

	public function TargetEvent()
	{
		// Определяем максимальное количество баз
		$iMaxBase = $this->db->fetchColumn("SELECT `fleet_base_tech` FROM game_users WHERE id = " . $this->_fleet['fleet_owner'] . "");

		$planet = new Planet();

		// Получение общего количества построенных баз
		$iPlanetCount = $this->db->fetchColumn("SELECT count(*) as num FROM game_planets WHERE `id_owner` = '" . $this->_fleet['fleet_owner'] . "' AND `planet_type` = '5'");

		$TargetAdress = sprintf(_getText('sys_adress_planet'), $this->_fleet['fleet_end_galaxy'], $this->_fleet['fleet_end_system'], $this->_fleet['fleet_end_planet']);

		// Если в галактике пусто (планета не заселена)
		if ($planet->isPositionFree($this->_fleet['fleet_end_galaxy'], $this->_fleet['fleet_end_system'], $this->_fleet['fleet_end_planet']))
		{
			// Если лимит баз исчерпан
			if ($iPlanetCount >= $iMaxBase)
			{
				$TheMessage = _getText('sys_colo_arrival') . $TargetAdress . _getText('sys_colo_maxcolo') . $iMaxBase . _getText('sys_base_planet');

				$this->game->sendMessage($this->_fleet['fleet_owner'], 0, $this->_fleet['fleet_start_time'], 0, _getText('sys_base_mess_from'), $TheMessage);

				$this->ReturnFleet();
			}
			else
			{
				// Создание планеты-базы
				$NewOwnerPlanet = $planet->createPlanet($this->_fleet['fleet_end_galaxy'], $this->_fleet['fleet_end_system'], $this->_fleet['fleet_end_planet'], $this->_fleet['fleet_owner'], _getText('sys_base_defaultname'), false, true);

				// Если планета-база создана
				if ($NewOwnerPlanet !== false)
				{
					$TheMessage = _getText('sys_colo_arrival') . $TargetAdress . _getText('sys_base_allisok');

					$this->game->sendMessage($this->_fleet['fleet_owner'], 0, $this->_fleet['fleet_start_time'], 0, _getText('sys_base_mess_from'), $TheMessage);

					$NewFleet = "";

					$fleetData = Fleet::unserializeFleet($this->_fleet['fleet_array']);

					foreach ($fleetData as $shipId => $shipArr)
					{
						if ($shipId == 216 && $shipArr['cnt'] > 0)
							$NewFleet .= $shipId . "," . ($shipArr['cnt'] - 1) . "!0;";
						elseif ($shipArr['cnt'] > 0)
							$NewFleet .= $shipId . "," . $shipArr['cnt'] . "!;";
					}

					$this->_fleet['fleet_array'] = $NewFleet;
					$this->_fleet['fleet_end_type'] = 5;

					$this->RestoreFleetToPlanet(false);
					$this->KillFleet();

					$this->cache->delete('app::planetlist_'.$this->_fleet['fleet_owner']);
				}
				else
				{
					$this->ReturnFleet();

					$TheMessage = _getText('sys_colo_arrival') . $TargetAdress . _getText('sys_base_badpos');

					$this->game->sendMessage($this->_fleet['fleet_owner'], 0, $this->_fleet['fleet_start_time'], 0, _getText('sys_base_mess_from'), $TheMessage);
				}
			}
		}
		else
		{
			$this->ReturnFleet();

			$TheMessage = _getText('sys_colo_arrival') . $TargetAdress . _getText('sys_base_notfree');

			$this->game->sendMessage($this->_fleet['fleet_owner'], 0, $this->_fleet['fleet_end_time'], 0, _getText('sys_base_mess_from'), $TheMessage);
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