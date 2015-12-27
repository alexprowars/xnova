<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2013 XNova Game Group
 * @var $this \Xnova\pageHelper
 * @var $user user
 * @var $resource array
 * @var $reslist array
 * @var $CombatCaps array
 * @var app::$planetrow planet
 * ICQ: 8696096, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xcms\db;
use Xcms\request;
use Xcms\sql;
use Xcms\strings;
use Xnova\User;
use Xnova\app;

if (!defined("INSIDE"))
	die("attemp hacking");

if ($this->user->data['urlaubs_modus_time'] > 0)
	$this->message("Нет доступа!");

if (!isset($_POST['crc']) || ($_POST['crc'] != md5($this->user->data['id'] . '-CHeAT_CoNTROL_Stage_02-' . date("dmY", time()) . '-' . $_POST["usedfleet"])))
	$this->message('Ошибка контрольной суммы!');

strings::includeLang('fleet');

if (isset($_POST['moon']) && intval($_POST['moon']) != $this->planet->data['id'] && ($this->planet->data['planet_type'] == 3 || $this->planet->data['planet_type'] == 5) && $this->planet->data['sprungtor'] > 0)
{
	$RestString = GetNextJumpWaitTime($this->planet->data);
	$NextJumpTime = $RestString['value'];
	$JumpTime = time();

	if ($NextJumpTime == 0)
	{
		$TargetPlanet = intval($_POST['moon']);
		$TargetGate = $this->db->query("SELECT `id`, `planet_type`, `sprungtor`, `last_jump_time` FROM game_planets WHERE `id` = '" . $TargetPlanet . "';", true);

		if (($TargetGate['planet_type'] == 3 || $TargetGate['planet_type'] == 5) && $TargetGate['sprungtor'] > 0)
		{
			$RestString = GetNextJumpWaitTime($TargetGate);

			if ($RestString['value'] == 0)
			{
				$ShipArray = array();
				$SubQueryOri = array();
				$SubQueryDes = array();

				foreach ($reslist['fleet'] AS $Ship)
				{
					$ShipLabel = "ship" . $Ship;

					if (!isset($_POST[$ShipLabel]) || !is_numeric($_POST[$ShipLabel]) || intval($_POST[$ShipLabel]) < 0)
						continue;

					if (abs(intval($_POST[$ShipLabel])) > $this->planet->data[$resource[$Ship]])
						$ShipArray[$Ship] = $this->planet->data[$resource[$Ship]];
					else
						$ShipArray[$Ship] = abs(intval($_POST[$ShipLabel]));

					if ($ShipArray[$Ship] != 0)
					{
						$SubQueryOri['-'.$resource[$Ship]] = $ShipArray[$Ship];
						$SubQueryDes['+'.$resource[$Ship]] = $ShipArray[$Ship];
					}
				}

				if (count($SubQueryOri))
				{
					$SubQueryOri['last_jump_time'] = $JumpTime;
					$SubQueryDes['last_jump_time'] = $JumpTime;

					sql::build()->update('game_planets')->set($SubQueryOri)->where('id', '=', $this->planet->data['id'])->execute();
					sql::build()->update('game_planets')->set($SubQueryDes)->where('id', '=', $TargetGate['id'])->execute();

					sql::build()->update('game_users')->setField('current_planet', $TargetGate['id'])->where('id', '=', $this->user->data['id'])->execute();

					$this->planet->data['last_jump_time'] = $JumpTime;
					$RestString = GetNextJumpWaitTime($this->planet->data);

					$RetMessage = _getText('gate_jump_done') . " - " . $RestString['string'];
				}
				else
					$RetMessage = _getText('gate_wait_data');
			}
			else
				$RetMessage = _getText('gate_wait_dest') . " - " . $RestString['string'];
		}
		else
			$RetMessage = _getText('gate_no_dest_g');
	}
	else
		$RetMessage = _getText('gate_wait_star') . " - " . $RestString['string'];

	$this->message($RetMessage, 'Результат', "?set=fleet", 5);
}

$parse = array();

$galaxy 	= request::P('galaxy', 0, VALUE_INT);
$system 	= request::P('system', 0, VALUE_INT);
$planet		= request::P('planet', 0, VALUE_INT);
$type 		= request::P('planettype', 0, VALUE_INT);
$acs 		= request::P('acs', 0, VALUE_INT);

$fleetmission 	= request::P('target_mission', 0, VALUE_INT);
$fleetarray 	= json_decode(base64_decode(str_rot13(request::P('usedfleet', ''))), true);

$YourPlanet = false;
$UsedPlanet = false;

$TargetPlanet = $this->db->query("SELECT * FROM game_planets WHERE `galaxy` = '" . $galaxy . "' AND `system` = '" . $system . "' AND `planet` = '" . $planet . "' AND `planet_type` = '" . $type . "'", true);

if ($galaxy == $TargetPlanet['galaxy'] && $system == $TargetPlanet['system'] && $planet == $TargetPlanet['planet'] && $type == $TargetPlanet['planet_type'])
{
	if ($TargetPlanet['id_owner'] == $this->user->data['id'] || ($this->user->data['ally_id'] > 0 && $TargetPlanet['id_ally'] == $this->user->data['ally_id']))
	{
		$YourPlanet = true;
		$UsedPlanet = true;
	}
	else
		$UsedPlanet = true;
}

$missiontype = getFleetMissions($fleetarray, Array($galaxy, $system, $planet, $type), $YourPlanet, $UsedPlanet, ($acs > 0));

if ($TargetPlanet['id_owner'] == 1 || $this->user->isAdmin())
	$missiontype[4] = _getText('type_mission', 4);

$SpeedFactor = GetGameSpeedFactor();
$AllFleetSpeed = GetFleetMaxSpeed($fleetarray, 0, user::get());
$GenFleetSpeed = intval($_POST['speed']);
$MaxFleetSpeed = min($AllFleetSpeed);

$distance = GetTargetDistance($this->planet->data['galaxy'], $_POST['galaxy'], $this->planet->data['system'], $_POST['system'], $this->planet->data['planet'], $_POST['planet']);
$duration = GetMissionDuration($GenFleetSpeed, $MaxFleetSpeed, $distance, $SpeedFactor);
$consumption = GetFleetConsumption($fleetarray, $SpeedFactor, $duration, $distance, user::get());

$stayConsumption = GetFleetStay($fleetarray);

if ($this->user->data['rpg_meta'] > time())
	$stayConsumption = ceil($stayConsumption * 0.9);

$parse['missions_selected'] = array();
$parse['missions'] = array();

if (count($missiontype) > 0)
{
	$i = 0;

	foreach ($missiontype as $a => $b)
	{
		if (($fleetmission > 0 && $fleetmission == $a) || (!isset($missiontype[$fleetmission]) && $i == 0) || count($missiontype) == 1)
			$parse['missions_selected'] = $a;

		$parse['missions'][$a] = $b;

		$i++;
	}
}

$parse['thisresource1'] = floor($this->planet->data["metal"]);
$parse['thisresource2'] = floor($this->planet->data["crystal"]);
$parse['thisresource3'] = floor($this->planet->data["deuterium"]);
$parse['consumption'] = $consumption;
$parse['stayConsumption'] = $stayConsumption;
$parse['dist'] = $distance;
$parse['acs'] = $acs;
$parse['thisgalaxy'] = $this->planet->data['galaxy'];
$parse['thissystem'] = $this->planet->data['system'];
$parse['thisplanet'] = $this->planet->data['planet'];
$parse['galaxy'] = $_POST["galaxy"];
$parse['system'] = $_POST["system"];
$parse['planet'] = $_POST["planet"];
$parse['planettype'] = $_POST["planettype"];
$parse['speed'] = $_POST["speed"];
$parse['usedfleet'] = $_POST["usedfleet"];
$parse['maxepedition'] = $_POST["maxepedition"];
$parse['curepedition'] = $_POST["curepedition"];
$parse['crc'] = md5($this->user->data['id'] . '-CHeAT_CoNTROL_Stage_03-' . date("dmY", time()) . '-' . $_POST["usedfleet"]);

$parse['ships'] = array();

foreach ($fleetarray as $i => $count)
{
	$ship = array
	(
		'id' => $i,
		'count' => $count,
		'consumption' => GetShipConsumption($i, user::get()),
		'speed' => GetFleetMaxSpeed("", $i, user::get()),
		'stay' => $CombatCaps[$i]['stay'],
	);

	if (isset($this->user->data['fleet_' . $i]) && isset($CombatCaps[$i]['power_consumption']) && $CombatCaps[$i]['power_consumption'] > 0)
		$ship['capacity'] = round($CombatCaps[$i]['capacity'] * (1 + $this->user->data['fleet_' . $i] * ($CombatCaps[$i]['power_consumption'] / 100)));
	else
		$ship['capacity'] = $CombatCaps[$i]['capacity'];

	$parse['ships'][] = $ship;
}

if (isset($missiontype[15]))
	$parse['expedition_hours'] = round($this->user->data[$resource[124]] / 2) + 1;

$this->setTemplate('fleet/stage_2');
$this->set('parse', $parse);

$this->setTitle(_getText('fl_title_2'));
$this->setContent();
$this->display();

?>