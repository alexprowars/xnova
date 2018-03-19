<?php

namespace Xnova\Missions;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\FleetEngine;
use Xnova\Galaxy;
use Xnova\User;
use Xnova\Models\User as UserModel;

class MissionCaseCreateBase extends FleetEngine implements Mission
{
	public function TargetEvent()
	{
		$owner = UserModel::findFirst($this->_fleet->owner);

		// Определяем максимальное количество баз
		$maxBases = $owner->getTechLevel('fleet_base');

		$galaxy = new Galaxy();

		// Получение общего количества построенных баз
		$iPlanetCount = $this->db->fetchColumn("SELECT count(*) as num FROM game_planets WHERE id_owner = '" . $this->_fleet->owner . "' AND planet_type = '5'");

		$TargetAdress = sprintf(_getText('sys_adress_planet'), $this->_fleet->end_galaxy, $this->_fleet->end_system, $this->_fleet->end_planet);

		// Если в галактике пусто (планета не заселена)
		if ($galaxy->isPositionFree($this->_fleet->end_galaxy, $this->_fleet->end_system, $this->_fleet->end_planet))
		{
			// Если лимит баз исчерпан
			if ($iPlanetCount >= $maxBases)
			{
				$TheMessage = _getText('sys_colo_arrival') . $TargetAdress . _getText('sys_colo_maxcolo') . $maxBases . _getText('sys_base_planet');

				User::sendMessage($this->_fleet->owner, 0, $this->_fleet->start_time, 0, _getText('sys_base_mess_from'), $TheMessage);

				$this->ReturnFleet();
			}
			else
			{
				// Создание планеты-базы
				$NewOwnerPlanet = $galaxy->createPlanet($this->_fleet->end_galaxy, $this->_fleet->end_system, $this->_fleet->end_planet, $this->_fleet->owner, _getText('sys_base_defaultname'), false, true);

				// Если планета-база создана
				if ($NewOwnerPlanet !== false)
				{
					$TheMessage = _getText('sys_colo_arrival') . $TargetAdress . _getText('sys_base_allisok');

					User::sendMessage($this->_fleet->owner, 0, $this->_fleet->start_time, 0, _getText('sys_base_mess_from'), $TheMessage);

					$NewFleet = "";

					$fleetData = $this->_fleet->getShips();

					foreach ($fleetData as $shipId => $shipArr)
					{
						if ($shipId == 216 && $shipArr['cnt'] > 0)
							$NewFleet .= $shipId . "," . ($shipArr['cnt'] - 1) . "!0;";
						elseif ($shipArr['cnt'] > 0)
							$NewFleet .= $shipId . "," . $shipArr['cnt'] . "!;";
					}

					$this->_fleet->fleet_array = $NewFleet;
					$this->_fleet->end_type = 5;

					$this->RestoreFleetToPlanet(false);
					$this->KillFleet();

					$this->cache->delete('app::planetlist_'.$this->_fleet->owner);
				}
				else
				{
					$this->ReturnFleet();

					$TheMessage = _getText('sys_colo_arrival') . $TargetAdress . _getText('sys_base_badpos');

					User::sendMessage($this->_fleet->owner, 0, $this->_fleet->start_time, 0, _getText('sys_base_mess_from'), $TheMessage);
				}
			}
		}
		else
		{
			$this->ReturnFleet();

			$TheMessage = _getText('sys_colo_arrival') . $TargetAdress . _getText('sys_base_notfree');

			User::sendMessage($this->_fleet->owner, 0, $this->_fleet->end_time, 0, _getText('sys_base_mess_from'), $TheMessage);
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