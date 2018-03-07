<?php

namespace Xnova\Missions;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\FleetEngine;
use Xnova\Galaxy;
use Xnova\Models\Planet;
use Xnova\Models\User as UserModel;
use Xnova\User;

class MissionCaseColonisation extends FleetEngine implements Mission
{
	public function TargetEvent()
	{
		$owner = UserModel::findFirst($this->_fleet->owner);

		$maxPlanets = $owner->getTechLevel('colonisation') + 1;

		if ($maxPlanets > $this->config->game->get('maxPlanets', 9))
			$maxPlanets = $this->config->game->get('maxPlanets', 9);

		$galaxy = new Galaxy();

		$iPlanetCount = Planet::count(['id_owner = ?0 AND planet_type = 1', 'bind' => [$this->_fleet->owner]]);

		$TargetAdress = sprintf(_getText('sys_adress_planet'), $this->_fleet->end_galaxy, $this->_fleet->end_system, $this->_fleet->end_planet);

		if ($galaxy->isPositionFree($this->_fleet->end_galaxy, $this->_fleet->end_system, $this->_fleet->end_planet))
		{
			if ($iPlanetCount >= $maxPlanets)
			{
				$TheMessage = _getText('sys_colo_arrival') . $TargetAdress . _getText('sys_colo_maxcolo') . $maxPlanets . _getText('sys_colo_planet');

				User::sendMessage($this->_fleet->owner, 0, $this->_fleet->start_time, 0, _getText('sys_colo_mess_from'), $TheMessage);

				$this->ReturnFleet();
			}
			else
			{
				$NewOwnerPlanet = $galaxy->createPlanet($this->_fleet->end_galaxy, $this->_fleet->end_system, $this->_fleet->end_planet, $this->_fleet->owner, _getText('sys_colo_defaultname'), false);

				if ($NewOwnerPlanet !== false)
				{
					$TheMessage = _getText('sys_colo_arrival') . $TargetAdress . _getText('sys_colo_allisok');

					User::sendMessage($this->_fleet->owner, 0, $this->_fleet->start_time, 0, _getText('sys_colo_mess_from'), $TheMessage);

					$NewFleet = "";

					$fleetData = $this->_fleet->getShips();

					foreach ($fleetData as $shipId => $shipArr)
					{
						if ($shipId == 208 && $shipArr['cnt'] > 0)
							$NewFleet .= $shipId . "," . ($shipArr['cnt'] - 1) . "!0;";
						elseif ($shipArr['cnt'] > 0)
							$NewFleet .= $shipId . "," . $shipArr['cnt'] . "!;";
					}

					$this->_fleet->fleet_array = $NewFleet;

					$this->RestoreFleetToPlanet(false);
					$this->KillFleet();

					$this->cache->delete('app::planetlist_'.$this->_fleet->owner);
				}
				else
				{
					$this->ReturnFleet();

					$TheMessage = _getText('sys_colo_arrival') . $TargetAdress . _getText('sys_colo_badpos');

					User::sendMessage($this->_fleet->owner, 0, $this->_fleet->start_time, 0, _getText('sys_colo_mess_from'), $TheMessage);
				}
			}
		}
		else
		{
			$this->ReturnFleet();

			$TheMessage = _getText('sys_colo_arrival') . $TargetAdress . _getText('sys_colo_notfree');

			User::sendMessage($this->_fleet->owner, 0, $this->_fleet->end_time, 0, _getText('sys_colo_mess_from'), $TheMessage);
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