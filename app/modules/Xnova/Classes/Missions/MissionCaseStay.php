<?php

namespace Xnova\Missions;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\FleetEngine;
use Xnova\Format;
use Xnova\User;

class MissionCaseStay extends FleetEngine implements Mission
{
	public function TargetEvent()
	{
		$TargetPlanet = $this->db->query("SELECT id_owner FROM game_planets WHERE galaxy = '" . $this->_fleet->end_galaxy . "' AND system = '" . $this->_fleet->end_system . "' AND planet = '" . $this->_fleet->end_planet . "' AND planet_type = '" . $this->_fleet->end_type . "'")->fetch();

		if ($TargetPlanet['id_owner'] != $this->_fleet->target_owner)
			$this->ReturnFleet();
		else
		{
			$this->RestoreFleetToPlanet(false);
			$this->KillFleet();

			$TargetAddedGoods = '';

			$fleetData = $this->_fleet->getShips();

			foreach ($fleetData as $shipId => $shipArr)
			{
				$TargetAddedGoods .= ', ' . _getText('tech', $shipId) . ': ' . $shipArr['count'];
			}

			$TargetMessage = sprintf(_getText('sys_stat_mess'),
								$this->_fleet->getTargetAdressLink(),
								Format::number($this->_fleet->resource_metal), _getText('Metal'),
								Format::number($this->_fleet->resource_crystal), _getText('Crystal'),
								Format::number($this->_fleet->resource_deuterium), _getText('Deuterium'));

			if ($TargetAddedGoods != '')
				$TargetMessage .= '<br>'.trim(substr($TargetAddedGoods, 1));

			User::sendMessage($this->_fleet->target_owner, 0, $this->_fleet->start_time, 5, _getText('sys_mess_qg'), $TargetMessage);
		}
	}

	public function EndStayEvent()
	{
		return;
	}

	public function ReturnEvent()
	{
		$TargetPlanet = $this->db->query("SELECT id_owner FROM game_planets WHERE galaxy = '" . $this->_fleet->start_galaxy . "' AND system = '" . $this->_fleet->start_system . "' AND planet = '" . $this->_fleet->start_planet . "' AND planet_type = '" . $this->_fleet->start_type . "';")->fetch();

		if ($TargetPlanet['id_owner'] != $this->_fleet->owner)
			$this->KillFleet();
		else
		{
			$this->RestoreFleetToPlanet();
			$this->KillFleet();

			$TargetAddedGoods = sprintf(_getText('sys_stay_mess_goods'), _getText('Metal'), Format::number($this->_fleet->resource_metal), _getText('Crystal'), Format::number($this->_fleet->resource_crystal), _getText('Deuterium'), Format::number($this->_fleet->resource_deuterium));

			$fleetData = $this->_fleet->getShips();

			foreach ($fleetData as $shipId => $shipArr)
			{
				$TargetAddedGoods .= ', ' . _getText('tech', $shipId) . ': ' . $shipArr['count'];
			}

			$TargetMessage = _getText('sys_stay_mess_back') . $this->_fleet->getTargetAdressLink() . _getText('sys_stay_mess_bend') . "<br />" . $TargetAddedGoods;

			User::sendMessage($this->_fleet->owner, 0, $this->_fleet->end_time, 5, _getText('sys_mess_qg'), $TargetMessage);
		}
	}
}