<?php

namespace Xnova\Controllers\Fleet;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Controllers\FleetController;
use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\RedirectException;
use Xnova\Fleet;
use Xnova\Format;
use Friday\Core\Lang;
use Xnova\Models\Planet;
use Xnova\Request;
use Xnova\Vars;

class StageTwo
{
	public function show (FleetController $controller)
	{
		if ($controller->user->vacation > 0)
			throw new ErrorException("Нет доступа!");

		Lang::includeLang('fleet', 'xnova');

		if ($controller->request->hasPost('moon') && $controller->request->getPost('moon', 'int') != $controller->planet->id && ($controller->planet->planet_type == 3 || $controller->planet->planet_type == 5) && $controller->planet->sprungtor > 0)
		{
			$nextJumpTime = $controller->planet->getNextJumpTime();

			if ($nextJumpTime == 0)
			{
				$TargetGate = Planet::findFirst($controller->request->getPost('moon', 'int'));

				if (($TargetGate->planet_type == 3 || $TargetGate->planet_type == 5) && $TargetGate->sprungtor > 0)
				{
					$nextJumpTime = $TargetGate->getNextJumpTime();

					if ($nextJumpTime == 0)
					{
						$ShipArray = [];

						foreach (Vars::getItemsByType(Vars::ITEM_TYPE_FLEET) AS $Ship)
						{
							$ShipLabel = "ship" . $Ship;

							if (!isset($_POST[$ShipLabel]) || !is_numeric($_POST[$ShipLabel]) || intval($_POST[$ShipLabel]) < 0)
								continue;

							if (abs(intval($_POST[$ShipLabel])) > $controller->planet->getUnitCount($Ship))
								$ShipArray[$Ship] = $controller->planet->getUnitCount($Ship);
							else
								$ShipArray[$Ship] = abs(intval($_POST[$ShipLabel]));

							if ($ShipArray[$Ship] != 0)
							{
								$controller->planet->setUnit($Ship, -$ShipArray[$Ship], true);
								$TargetGate->setUnit($Ship, $ShipArray[$Ship], true);
							}
							else
								unset($ShipArray[$Ship]);
						}

						if (count($ShipArray))
						{
							$controller->planet->last_jump_time = time();
							$controller->planet->update();

							$TargetGate->last_jump_time = time();
							$TargetGate->update();

							$controller->user->update(['planet_current' => $TargetGate->id]);

							$RetMessage = _getText('gate_jump_done') . " - " . Format::time($controller->planet->getNextJumpTime());
						}
						else
							$RetMessage = _getText('gate_wait_data');
					}
					else
						$RetMessage = _getText('gate_wait_dest') . " - " . Format::time($nextJumpTime);
				}
				else
					$RetMessage = _getText('gate_no_dest_g');
			}
			else
				$RetMessage = _getText('gate_wait_star') . " - " . Format::time($nextJumpTime);

			throw new RedirectException($RetMessage, 'Результат', "/fleet/", 5);
		}

		$parse = [];

		$galaxy = (int) $controller->request->getPost('galaxy', 'int', 0);
		$system = (int) $controller->request->getPost('system', 'int', 0);
		$planet = (int) $controller->request->getPost('planet', 'int', 0);
		$type 	= (int) $controller->request->getPost('planet_type', 'int', 0);
		$acs 	= (int) $controller->request->getPost('alliance', 'int', 0);

		$fleetmission 	= (int) $controller->request->getPost('mission', 'int', 0);
		$fleetarray 	= json_decode(base64_decode(str_rot13($controller->request->getPost('fleet', null, ''))), true);

		$YourPlanet = false;
		$UsedPlanet = false;

		$TargetPlanet = Planet::findByCoords($galaxy, $system, $planet, $type);

		if ($TargetPlanet)
		{
			if ($TargetPlanet->id_owner == $controller->user->id || ($controller->user->ally_id > 0 && $TargetPlanet->id_ally == $controller->user->ally_id))
			{
				$YourPlanet = true;
				$UsedPlanet = true;
			}
			else
				$UsedPlanet = true;
		}

		$missiontype = Fleet::getFleetMissions($fleetarray, [$galaxy, $system, $planet, $type], $YourPlanet, $UsedPlanet, ($acs > 0));

		if ($TargetPlanet && ($TargetPlanet->id_owner == 1 || $controller->user->isAdmin()))
			$missiontype[4] = _getText('type_mission', 4);

		$GenFleetSpeed = (int) $controller->request->getPost('speed', 'int', 10);
		$stayConsumption = Fleet::GetFleetStay($fleetarray);

		if ($controller->user->rpg_meta > time())
			$stayConsumption = ceil($stayConsumption * 0.9);

		$parse['mission'] = 0;
		$parse['missions'] = [];

		if (count($missiontype) > 0)
		{
			$i = 0;

			foreach ($missiontype as $a => $b)
			{
				if (($fleetmission > 0 && $fleetmission == $a) || (!isset($missiontype[$fleetmission]) && $i == 0) || count($missiontype) == 1)
					$parse['mission'] = $a;

				$parse['missions'][] = $a;

				$i++;
			}
		}

		$parse['galaxy'] = $galaxy;
		$parse['system'] = $system;
		$parse['planet'] = $planet;
		$parse['planet_type'] = $type;

		$parse['galaxy_current'] = (int) $controller->planet->galaxy;
		$parse['system_current'] = (int) $controller->planet->system;
		$parse['planet_current'] = (int) $controller->planet->planet;

		$parse['resources'] = [
			'metal' => floor($controller->planet->metal),
			'crystal' => floor($controller->planet->crystal),
			'deuterium' => floor($controller->planet->deuterium)
		];

		$parse['hold'] = $stayConsumption;
		$parse['alliance'] = $acs;
		$parse['speed'] = $GenFleetSpeed;
		$parse['fleet'] = $controller->request->getPost('fleet', null, '');

		$parse['ships'] = [];

		foreach ($fleetarray as $i => $count)
		{
			$ship = $controller->getShipInfo($i);
			$ship['count'] = $count;

			$parse['ships'][] = $ship;
		}

		if (isset($missiontype[15]))
			$parse['expedition_hours'] = round($controller->user->getTechLevel('expedition') / 2) + 1;

		Request::addData('page', $parse);

		$controller->tag->setTitle(_getText('fl_title_2'));
	}
}