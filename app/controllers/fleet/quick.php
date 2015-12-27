<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2013 XNova Game Group
 * @var $this \Xnova\pageHelper
 * @var $CombatCaps array
 * @var $resource array
 * @var $HeDBRec array
 * ICQ: 8696096, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xcms\core;
use Xcms\db;
use Xcms\request;
use Xcms\strings;
use Xnova\User;
use Xnova\app;

if (!defined("INSIDE"))
	die("attemp hacking");

if ($this->user->data['urlaubs_modus_time'] > 0)
	die("Нет доступа!");

strings::includeLang('fleet');

$maxfleet = db::first($this->db->query("SELECT COUNT(fleet_owner) AS `actcnt` FROM game_fleets WHERE `fleet_owner` = '" . $this->user->data['id'] . "';", true));

$MaxFlottes = 1 + $this->user->data[$resource[108]];
if ($this->user->data['rpg_admiral'] > time())
	$MaxFlottes += 2;

if ($MaxFlottes <= $maxfleet)
	die('Все слоты флота заняты');

$Mode 	= request::G('mode', VALUE_INT, 0);
$Galaxy = request::G('g', VALUE_INT, 0);
$System = request::G('s', VALUE_INT, 0);
$Planet = request::G('p', VALUE_INT, 0);
$TypePl = request::G('t', VALUE_INT, 0);
$num 	= request::G('count', VALUE_INT, 0);

if ($Galaxy > MAX_GALAXY_IN_WORLD || $Galaxy < 1)
	die('Ошибочная галактика!');
if ($System > MAX_SYSTEM_IN_GALAXY || $System < 1)
	die('Ошибочная система!');
if ($Planet > MAX_PLANET_IN_SYSTEM || $Planet < 1)
	die('Ошибочная планета!');
if ($TypePl != 1 && $TypePl != 2 && $TypePl != 3 && $TypePl != 5)
	die('Ошибочный тип планеты!');

if ($this->planet->data['galaxy'] == $Galaxy && $this->planet->data['system'] == $System && $this->planet->data['planet'] == $Planet && $this->planet->data['planet_type'] == $TypePl)
	$target = $this->planet->data;
else
{
	$target = $this->db->query("SELECT * FROM game_planets WHERE galaxy = " . $Galaxy . " AND system = " . $System . " AND planet = " . $Planet . " AND (planet_type = " . (($TypePl == 2) ? "1 OR planet_type = 5" : $TypePl) . ")", true);

	if (!isset($target['id']))
		die('Цели не существует!');
}

$FleetArray = array();
$FleetSpeed = 0;

if ($Mode == 6 && ($TypePl == 1 || $TypePl == 3 || $TypePl == 5))
{
	if ($num <= 0)
		die('Вы были забанены за читерство!');
	if ($this->planet->data['spy_sonde'] == 0)
		die('Нет шпионских зондов ля отправки!');
	if ($target['id_owner'] == $this->user->data['id'])
		die('Невозможно выполнить задание!');

	$HeDBRec = $this->db->query("SELECT id, onlinetime, urlaubs_modus_time FROM game_users WHERE `id` = '" . $target['id_owner'] . "';", true);

	$UserPoints  = $this->db->query("SELECT total_points FROM game_statpoints WHERE `stat_type` = '1' AND `stat_code` = '1' AND `id_owner` = '" . $this->user->data['id'] . "';", true);
	$User2Points = $this->db->query("SELECT total_points FROM game_statpoints WHERE `stat_type` = '1' AND `stat_code` = '1' AND `id_owner` = '" . $HeDBRec['id'] . "';", true);

	$MyGameLevel = $UserPoints['total_points'];
	$HeGameLevel = $User2Points['total_points'];

	if (!$HeGameLevel)
		$HeGameLevel = 0;

	if ($HeDBRec['onlinetime'] < (time() - 60 * 60 * 24 * 7))
		$NoobNoActive = 1;
	else
		$NoobNoActive = 0;

	if ($this->user->data['authlevel'] != 3)
	{
		if (isset($TargetPlanet['id_owner'])  AND $NoobNoActive == 0 AND $HeGameLevel < (core::getConfig('noobprotectiontime') * 1000))
		{
			if ($MyGameLevel > ($HeGameLevel * core::getConfig('noobprotectionmulti')))
				die('Игрок находится под защитой новичков!');
			if (($MyGameLevel * core::getConfig('noobprotectionmulti')) < $HeGameLevel)
				die('Вы слишком слабы для нападения на этого игрока!');
		}
	}

	if ($HeDBRec['urlaubs_modus_time'] > 0)
		die('Игрок в режиме отпуска!');

	if ($this->planet->data['spy_sonde'] < $num)
		$num = $this->planet->data['spy_sonde'];

	$FleetArray[210] = $num;

	$FleetSpeed = min(GetFleetMaxSpeed($FleetArray, 0, user::get()));

}
elseif ($Mode == 8 && $TypePl == 2)
{
	$DebrisSize = $target['debris_metal'] + $target['debris_crystal'];

	if ($DebrisSize == 0)
		die('Нет обломков для сбора!');
	if ($this->planet->data['recycler'] == 0)
		die('Нет переработчиков для сбора обломков!');

	$RecyclerNeeded = 0;

	if ($this->planet->data['recycler'] > 0 && $DebrisSize > 0)
	{
		$RecyclerNeeded = floor($DebrisSize / ($CombatCaps[209]['capacity'] * (1 + $this->user->data['fleet_209'] * ($CombatCaps[209]['power_consumption'] / 100)))) + 1;

		if ($RecyclerNeeded > $this->planet->data['recycler'])
			$RecyclerNeeded = $this->planet->data['recycler'];
	}

	if ($RecyclerNeeded > 0)
	{
		$FleetArray[209] = $RecyclerNeeded;

		$FleetSpeed = min(GetFleetMaxSpeed($FleetArray, 0, user::get()));
	}
	else
		die('Произошла какая-то непонятная ситуация');
}
else
	die('Такой миссии не существует!');

if ($FleetSpeed > 0 && count($FleetArray) > 0)
{
	$SpeedFactor = core::getConfig('fleet_speed') / 2500;
	$distance = GetTargetDistance($this->planet->data['galaxy'], $Galaxy, $this->planet->data['system'], $System, $this->planet->data['planet'], $Planet);
	$duration = GetMissionDuration(10, $FleetSpeed, $distance, $SpeedFactor);

	$consumption = GetFleetConsumption($FleetArray, $SpeedFactor, $duration, $distance, user::get());

	$ShipCount = 0;
	$ShipArray = '';
	$FleetSubQRY = '';
	$FleetStorage = 0;

	foreach ($FleetArray as $Ship => $Count)
	{
		$FleetSubQRY .= "`" . $resource[$Ship] . "` = `" . $resource[$Ship] . "` - " . $Count . " , ";
		$ShipArray .=  $Ship . "," . $Count . "!" . (isset($this->user->data['fleet_' . $Ship]) ? $this->user->data['fleet_' . $Ship] : 0) . ";";
		$ShipCount += $Count;

		if (isset($this->user->data['fleet_' . $Ship]) && isset($CombatCaps[$Ship]['power_consumption']) && $CombatCaps[$Ship]['power_consumption'] > 0)
			$FleetStorage += round($CombatCaps[$Ship]['capacity'] * (1 + $this->user->data['fleet_' . $Ship] * ($CombatCaps[$Ship]['power_consumption'] / 100))) * $Count;
		else
			$FleetStorage += $CombatCaps[$Ship]['capacity'] * $Count;
	}

	if ($FleetStorage < $consumption)
		die('Не хватает места в трюме для топлива! (необходимо еще ' . ($consumption - $FleetStorage) . ')');
	if ($this->planet->data['deuterium'] < $consumption)
		die('Не хватает топлива на полёт! (необходимо еще ' . ($consumption - $this->planet->data['deuterium']) . ')');

	if ($FleetSubQRY != '')
	{
		$QryInsertFleet = "INSERT INTO game_fleets SET ";
		$QryInsertFleet .= "`fleet_owner` = '" . $this->user->data['id'] . "', ";
		$QryInsertFleet .= "`fleet_owner_name` = '" . $this->planet->data['name'] . "', ";
		$QryInsertFleet .= "`fleet_mission` = '" . $Mode . "', ";
		$QryInsertFleet .= "`fleet_array` = '" . $ShipArray . "', ";
		$QryInsertFleet .= "`fleet_start_time` = '" . ($duration + time()) . "', ";
		$QryInsertFleet .= "`fleet_start_galaxy` = '" . $this->planet->data['galaxy'] . "', ";
		$QryInsertFleet .= "`fleet_start_system` = '" . $this->planet->data['system'] . "', ";
		$QryInsertFleet .= "`fleet_start_planet` = '" . $this->planet->data['planet'] . "', ";
		$QryInsertFleet .= "`fleet_start_type` = '" . $this->planet->data['planet_type'] . "', ";
		$QryInsertFleet .= "`fleet_end_time` = '" . (($duration * 2) + time()) . "', ";
		$QryInsertFleet .= "`fleet_end_galaxy` = '" . $Galaxy . "', ";
		$QryInsertFleet .= "`fleet_end_system` = '" . $System . "', ";
		$QryInsertFleet .= "`fleet_end_planet` = '" . $Planet . "', ";
		$QryInsertFleet .= "`fleet_end_type` = '" . $TypePl . "', ";

		if ($Mode == 6)
		{
			$QryInsertFleet .= "`fleet_target_owner` = '" . $HeDBRec['id'] . "', ";
			$QryInsertFleet .= "`fleet_target_owner_name` = '" . $target['name'] . "', ";
		}

		$QryInsertFleet .= "`start_time` = '" . time() . "', `fleet_time` = '" . ($duration + time()) . "';";
		$this->db->query($QryInsertFleet);

		$this->db->query("UPDATE game_planets SET " . $FleetSubQRY . " deuterium = deuterium - " . $consumption . " WHERE `id` = '" . $this->planet->data['id'] . "'");

		$tutorial = $this->db->query("SELECT id, quest_id FROM game_users_quests WHERE user_id = ".$this->user->getId()." AND finish = '0' AND stage = 0", true);

		if (isset($tutorial['id']))
		{
			strings::includeLang('tutorial');

			$quest = _getText('tutorial', $tutorial['quest_id']);

			foreach ($quest['TASK'] AS $taskKey => $taskVal)
			{
				if ($taskKey == 'FLEET_MISSION' && $taskVal == $Mode)
				{
					$this->db->query("UPDATE game_users_quests SET stage = 1 WHERE id = " . $tutorial['id'] . ";");
				}
			}
		}

		die("Флот отправлен на координаты [" . $Galaxy . ":" . $System . ":" . $Planet . "] с миссией " . _getText('type_mission', $Mode) . " и прибудет к цели в " . datezone("H:i:s", ($duration + time())) . "");
	}
}

?>