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
use Xcms\strings;
use Xnova\User;
use Xnova\app;

if (!defined("INSIDE"))
	die("attemp hacking");

if ($this->user->data['urlaubs_modus_time'] > 0)
	$this->message("Нет доступа!");

if (!isset($_POST['crc']) || ($_POST['crc'] != md5($this->user->data['id'] . '-CHeAT_CoNTROL_Stage_01-' . date("dmY", time()))))
	$this->message('Ошибка контрольной суммы!');

strings::includeLang('fleet');

$parse = array();

$speed = array(
	10 => 100,
	9 => 90,
	8 => 80,
	7 => 70,
	6 => 60,
	5 => 50,
	4 => 40,
	3 => 30,
	2 => 20,
	1 => 10,
);

$g = request::P('galaxy', 0, VALUE_INT);
$s = request::P('system', 0, VALUE_INT);
$p = request::P('planet', 0, VALUE_INT);
$t = request::P('planet_type', 0, VALUE_INT);

if (!$g)
	$g = $this->planet->data['galaxy'];

if (!$s)
	$s = $this->planet->data['system'];

if (!$p)
	$p = $this->planet->data['planet'];

if (!$t)
	$t = 1;

$FleetHiddenBlock = "";
$fleet['fleetlist'] = "";
$fleet['amount'] = 0;

foreach ($reslist['fleet'] as $n => $i)
{
	if (isset($_POST["ship" . $i]) && in_array($i, $reslist['fleet']) && intval($_POST["ship" . $i]) > 0)
	{
		if (intval($_POST["ship" . $i]) > $this->planet->data[$resource[$i]])
			continue;

		$fleet['fleetarray'][$i] = intval($_POST["ship" . $i]);
		$fleet['fleetlist'] .= $i . "," . intval($_POST["ship" . $i]) . ";";
		$fleet['amount'] += intval($_POST["ship" . $i]);

		$ship = array
		(
			'id' => $i,
			'count' => intval($_POST["ship" . $i]),
			'consumption' => GetShipConsumption($i, user::get()),
			'speed' => GetFleetMaxSpeed("", $i, user::get())
		);

		if (isset($this->user->data['fleet_' . $i]) && isset($CombatCaps[$i]['power_consumption']) && $CombatCaps[$i]['power_consumption'] > 0)
			$ship['capacity'] = round($CombatCaps[$i]['capacity'] * (1 + $this->user->data['fleet_' . $i] * ($CombatCaps[$i]['power_consumption'] / 100)));
		else
			$ship['capacity'] = $CombatCaps[$i]['capacity'];

		$parse['ships'][] = $ship;
	}
}

if (!$fleet['fleetlist'])
	$this->message(_getText('fl_unselectall'), _getText('fl_error'), "?set=fleet", 1);

$parse['usedfleet'] = str_rot13(base64_encode(json_encode($fleet['fleetarray'])));
$parse['thisgalaxy'] = $this->planet->data['galaxy'];
$parse['thissystem'] = $this->planet->data['system'];
$parse['thisplanet'] = $this->planet->data['planet'];
$parse['thistype'] = $this->planet->data['planet_type'];
$parse['galaxyend'] = $g;
$parse['systemend'] = $s;
$parse['planetend'] = $p;
$parse['typeend'] = $t;
$parse['thisresource1'] = floor($this->planet->data['metal']);
$parse['thisresource2'] = floor($this->planet->data['crystal']);
$parse['thisresource3'] = floor($this->planet->data['deuterium']);
$parse['speed'] = $speed;

$parse['shortcut'] = array();

$inf = $this->db->query("SELECT fleet_shortcut FROM game_users_info WHERE id = " . $this->user->data['id'] . ";", true);

if ($inf['fleet_shortcut'])
{
	$scarray = explode("\r\n", $inf['fleet_shortcut']);

	foreach ($scarray as $a => $b)
	{
		if ($b != '')
		{
			$c = explode(',', $b);

			$parse['shortcut'][] = $c;
		}
	}
}

$parse['planets'] = array();

$kolonien = $this->user->getUserPlanets($this->user->getId(), true, $this->user->data['ally_id']);

if (count($kolonien) > 1)
{
	foreach ($kolonien AS $row)
	{
		if ($row['id'] == $this->planet->data['id'])
			continue;

		if ($row['planet_type'] == 3)
			$row['name'] .= " " . _getText('fl_shrtcup3');

		$parse['planets'][] = $row;
	}
}

$parse['moon_timer'] = '';
$parse['moons'] = array();

if ($this->planet->data['planet_type'] == 3 || $this->planet->data['planet_type'] == 5)
{
	$moons = $this->db->query("SELECT `id`, `name`, `system`, `galaxy`, `planet`, `sprungtor`, `last_jump_time` FROM game_planets WHERE (`planet_type` = '3' OR `planet_type` = '5') AND " . $resource[43] . " > 0 AND id != ".$this->planet->data['id']." AND `id_owner` = '" . $this->user->data['id'] . "';");

	if (db::num_rows($moons))
	{
		$timer = GetNextJumpWaitTime($this->planet->data);

		if ($timer['value'] != 0)
			$parse['moon_timer'] = InsertJavaScriptChronoApplet("Gate", "1", $timer['value'], true);;

		while ($moon = db::fetch($moons))
		{
			$moon['timer'] = GetNextJumpWaitTime($moon);

			$parse['moons'][] = $moon;
		}
	}
}

$parse['aks'] = array();

$aks_madnessred = $this->db->query("SELECT a.* FROM game_aks a, game_aks_user au WHERE au.aks_id = a.id AND au.user_id = " . $this->user->data['id'] . " ;", '');

if (db::num_rows($aks_madnessred))
{
	while ($row = db::fetch($aks_madnessred))
	{
		$parse['aks'][] = $row;
	}
}

$parse['maxepedition'] = intval($_POST['maxepedition']);
$parse['curepedition'] = intval($_POST['curepedition']);
$parse['target_mission'] = intval($_POST['target_mission']);
$parse['crc'] =  md5($this->user->data['id'] . '-CHeAT_CoNTROL_Stage_02-' . date("dmY", time()) . '-' . str_rot13(base64_encode(json_encode($fleet['fleetarray']))));

$this->setTemplate('fleet/stage_1');
$this->set('parse', $parse);

$this->setTitle(_getText('fl_title_1'));
$this->setContent();
$this->display();

?>