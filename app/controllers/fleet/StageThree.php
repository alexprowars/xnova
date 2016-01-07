<?php
namespace App\Controllers\Fleet;

use App\Controllers\FleetController;
use App\Fleet;
use App\Helpers;
use App\Lang;
use App\Sql;

class StageThree
{
	public function show (FleetController $controller)
	{
		if ($controller->user->vacation > 0)
			$controller->message("Нет доступа!");

		if (!isset($_POST['crc']) || $_POST['crc'] != md5($controller->user->id . '-CHeAT_CoNTROL_Stage_03-' . date("dmY", time()) . '-' . $_POST["usedfleet"]))
			$controller->message('Ошибка контрольной суммы!');

		Lang::includeLang('fleet');

		$galaxy = $controller->request->getPost('galaxy', 'int', 0);
		$system = $controller->request->getPost('system', 'int', 0);
		$planet = $controller->request->getPost('planet', 'int', 0);
		$planettype = $controller->request->getPost('planettype', 'int', 0);

		$fleetmission = $controller->request->getPost('mission', 'int', 0);

		$fleetarray = json_decode(base64_decode(str_rot13($controller->request->getPost('usedfleet', null, ''))), true);

		if (!$fleetmission)
			$controller->message("<span class=\"error\"><b>Не выбрана миссия!</b></span>", 'Ошибка', "?set=fleet", 2);

		if (($fleetmission == 1 || $fleetmission == 6 || $fleetmission == 9 || $fleetmission == 2) && $controller->config->app->get('disableAttacks', 0) > 0 && time() < $controller->config->app->get('disableAttacks', 0))
			$controller->message("<span class=\"error\"><b>Посылать флот в атаку временно запрещено.<br>Дата включения атак " . $controller->game->datezone("d.m.Y H ч. i мин.", $controller->config->app->get('disableAttacks', 0)) . "</b></span>", 'Ошибка');

		$fleet_group_mr = 0;

		if ($_POST['acs'] > 0)
		{
			if ($fleetmission == 2)
			{
				$aks_count_mr = $controller->db->query("SELECT a.* FROM game_aks a, game_aks_user au WHERE au.aks_id = a.id AND au.user_id = " . $controller->user->id . " AND au.aks_id = " . intval($_POST['acs']) . "");

				if ($aks_count_mr->numRows() > 0)
				{
					$aks_tr = $aks_count_mr->fetch();

					if ($aks_tr['galaxy'] == $_POST["galaxy"] && $aks_tr['system'] == $_POST["system"] && $aks_tr['planet'] == $_POST["planet"] && $aks_tr['planet_type'] == $_POST["planettype"])
					{
						$fleet_group_mr = $_POST['acs'];
					}
				}
			}
		}
		if (($_POST['acs'] == 0 || $fleet_group_mr == 0) && ($fleetmission == 2))
		{
			$fleetmission = 1;
		}

		$protection = $controller->config->game->get('noobprotection');
		$protectiontime = $controller->config->game->get('noobprotectiontime');
		$protectionmulti = $controller->config->game->get('noobprotectionmulti');

		if ($protectiontime < 1)
			$protectiontime = 9999999999999999;

		if (!is_array($fleetarray))
		{
			$controller->message("<span class=\"error\"><b>Ошибка в передаче параметров!</b></span>", 'Ошибка', "?set=fleet", 2);
		}

		foreach ($fleetarray as $Ship => $Count)
		{
			if ($Count > $controller->planet->{$controller->game->resource[$Ship]})
				$controller->message("<span class=\"error\"><b>Недостаточно флота для отправки на планете!</b></span>", 'Ошибка', "?set=fleet", 2);
		}

		if ($planettype != 1 && $planettype != 2 && $planettype != 3 && $planettype != 5)
			$controller->message("<span class=\"error\"><b>Неизвестный тип планеты!</b></span>", 'Ошибка', "?set=fleet", 2);

		if ($controller->planet->galaxy == $galaxy && $controller->planet->system == $system && $controller->planet->planet == $planet && $controller->planet->planet_type == $planettype)
			$controller->message("<span class=\"error\"><b>Невозможно отправить флот на эту же планету!</b></span>", 'Ошибка', "?set=fleet", 2);

		if ($fleetmission == 8)
		{
			$select = $controller->db->query("SELECT * FROM game_planets WHERE galaxy = '" . $galaxy . "' AND system = '" . $system . "' AND planet = '" . $planet . "' AND (planet_type = 1 OR planet_type = 5)");
		}
		else
		{
			$select = $controller->db->query("SELECT * FROM game_planets WHERE galaxy = '" . $galaxy . "' AND system = '" . $system . "' AND planet = '" . $planet . "' AND planet_type = '" . $planettype . "'");
		}

		if ($fleetmission != 15)
		{
			if ($select->numRows() == 0 && $fleetmission != 7 && $fleetmission != 10)
				$controller->message("<span class=\"error\"><b>Данной планеты не существует!</b> - [".$galaxy.":".$system.":".$planet."]</span>", 'Ошибка #1', "?set=fleet", 20);
			elseif ($fleetmission == 9 && $select->numRows() == 0)
				$controller->message("<span class=\"error\"><b>Данной планеты не существует!</b> - [".$galaxy.":".$system.":".$planet."]</span>", 'Ошибка #2', "?set=fleet", 20);
			elseif ($select->numRows() == 0 && $fleetmission == 7 && $planettype != 1)
				$controller->message("<span class=\"error\"><b>Колонизировать можно только планету!</b></span>", 'Ошибка', "?set=fleet", 2);
		}
		else
		{
			if ($controller->user->{$controller->game->resource[124]} >= 1)
			{
				$maxexp = $controller->db->query("SELECT COUNT(*) AS `expeditions` FROM game_fleets WHERE `fleet_owner` = '" . $controller->user->id . "' AND `fleet_mission` = '15';")->fetch();

				$ExpeditionEnCours = $maxexp['expeditions'];
				$MaxExpedition = 1 + floor($controller->user->{$controller->game->resource[124]} / 3);
			}
			else
			{
				$MaxExpedition = 0;
				$ExpeditionEnCours = 0;
			}

			if ($controller->user->{$controller->game->resource[124]} == 0)
				$controller->message("<span class=\"error\"><b>Вами не изучена \"Экспедиционная технология\"!</b></span>", 'Ошибка', "?set=fleet", 2);
			elseif ($ExpeditionEnCours >= $MaxExpedition)
				$controller->message("<span class=\"error\"><b>Вы уже отправили максимальное количество экспедиций!</b></span>", 'Ошибка', "?set=fleet", 2);

			if (intval($_POST['expeditiontime']) <= 0 || intval($_POST['expeditiontime']) > (round($controller->user->{$controller->game->resource[124]} / 2) + 1))
				$controller->message("<span class=\"error\"><b>Вы не можете столько времени летать в экспедиции!</b></span>", 'Ошибка', "?set=fleet", 2);
		}

		$TargetPlanet = $select->fetch();

		if ($TargetPlanet['id_owner'] == $controller->user->id || ($controller->user->ally_id > 0 && $TargetPlanet['id_ally'] == $controller->user->ally_id))
		{
			$YourPlanet = true;
			$UsedPlanet = true;
		}
		elseif (!empty($TargetPlanet['id_owner']))
		{
			$YourPlanet = false;
			$UsedPlanet = true;
		}
		else
		{
			$YourPlanet = false;
			$UsedPlanet = false;
		}

		if ($fleetmission == 4 && ($TargetPlanet['id_owner'] == 1 || $controller->user->isAdmin()))
			$YourPlanet = true;

		$missiontype = Fleet::getFleetMissions($fleetarray, Array($galaxy, $system, $planet, $planettype), $YourPlanet, $UsedPlanet, ($fleet_group_mr > 0));

		if (!isset($missiontype[$fleetmission]))
			$controller->message("<span class=\"error\"><b>Миссия неизвестна!</b></span>", 'Ошибка', "?set=fleet", 2);

		if ($fleetmission == 8 && $TargetPlanet['debris_metal'] == 0 && $TargetPlanet['debris_crystal'] == 0)
		{
			if ($TargetPlanet['debris_metal'] == 0 && $TargetPlanet['debris_crystal'] == 0)
				$controller->message("<span class=\"error\"><b>Нет обломков для сбора.</b></span>", 'Ошибка', "?set=fleet", 2);
		}

		if (isset($TargetPlanet['id_owner']))
		{
			$HeDBRec = $controller->db->query("SELECT * FROM game_users WHERE `id` = '" . $TargetPlanet['id_owner'] . "';")->fetch();

			if (!isset($HeDBRec['id']))
				$controller->message("<span class=\"error\"><b>Неизвестная ошибка #FLTNFU".$TargetPlanet['id_owner']."</b></span>", 'Ошибка', "?set=fleet", 2);
		}
		else
			$HeDBRec = $controller->user->toArray();

		if (($HeDBRec['authlevel'] > 0 && $controller->user->authlevel == 0) && ($fleetmission != 4 && $fleetmission != 3))
			$controller->message("<span class=\"error\"><b>На этого игрока запрещено нападать</b></span>", 'Ошибка', "?set=fleet", 2);

		if ($controller->user->ally_id != 0 && $HeDBRec['ally_id'] != 0 && $fleetmission == 1)
		{
			$ad = $controller->db->query("SELECT * FROM game_alliance_diplomacy WHERE (a_id = " . $HeDBRec['ally_id'] . " AND d_id = " . $controller->user->ally_id . ") AND status = 1", true);

			if (isset($ad['id']) && $ad['type'] < 3)
				$controller->message("<span class=\"error\"><b>Заключён мир или перемирие с альянсом атакуемого игрока.</b></span>", "Ошибка дипломатии", "?set=fleet", 2);
		}

		$VacationMode = $HeDBRec['vacation'];

		if ($controller->user->authlevel < 2)
		{
			$MyGameLevel = $controller->db->fetchColumn("SELECT total_points FROM game_statpoints WHERE `stat_type` = '1' AND `stat_code` = '1' AND `id_owner` = '" . $controller->user->id . "'");
			$HeGameLevel = $controller->db->fetchColumn("SELECT total_points FROM game_statpoints WHERE `stat_type` = '1' AND `stat_code` = '1' AND `id_owner` = '" . $HeDBRec['id'] . "'");

			if (!$HeGameLevel)
				$HeGameLevel = 0;

			if ($HeDBRec['onlinetime'] < (time() - 60 * 60 * 24 * 7) || $HeDBRec['banned'] != 0)
				$NoobNoActive = 1;
			else
				$NoobNoActive = 0;

			if (isset($TargetPlanet['id_owner']) && ($fleetmission == 1 || $fleetmission == 2 || $fleetmission == 5 || $fleetmission == 6 || $fleetmission == 9) && $protection && !$NoobNoActive && $HeGameLevel < ($protectiontime * 1000))
			{
				if ($MyGameLevel > ($HeGameLevel * $protectionmulti))
					$controller->message("<span class=\"success\"><b>Игрок находится под защитой новичков!</b></span>", 'Защита новичков', "?set=fleet", 2);
				if (($MyGameLevel * $protectionmulti) < $HeGameLevel)
					$controller->message("<span class=\"success\"><b>Вы слишком слабы для нападения на этого игрока!</b></span>", 'Защита новичков', "?set=fleet", 2);
			}
		}

		if ($VacationMode && $fleetmission != 8)
			$controller->message("<span class=\"success\"><b>Игрок в режиме отпуска!</b></span>", 'Режим отпуска', "?set=fleet", 2);

		$flyingFleets = $controller->db->fetchColumn("SELECT COUNT(fleet_id) as Number FROM game_fleets WHERE `fleet_owner`='".$controller->user->id."'");

		$fleetmax = $controller->user->{$controller->game->resource[108]} + 1;

		if ($controller->user->rpg_admiral > time())
			$fleetmax += 2;

		if ($fleetmax <= $flyingFleets)
			$controller->message("Все слоты флота заняты. Изучите компьютерную технологию для увеличения кол-ва летящего флота.", "Ошибка", "?set=fleet", 2);

		if (($_POST['resource1'] + $_POST['resource2'] + $_POST['resource3']) < 1 AND $fleetmission == 3)
			$controller->message("<span class=\"success\"><b>Нет сырья для транспорта!</b></span>", _getText('type_mission', 3), "?set=fleet", 2);

		if ($fleetmission != 15)
		{
			if (!isset($TargetPlanet['id_owner']) AND $fleetmission < 7)
				$controller->message("<span class=\"error\"><b>Планеты не существует!</b></span>", 'Ошибка', "?set=fleet", 2);

			if (isset($TargetPlanet['id_owner']) AND ($fleetmission == 7 || $fleetmission == 10))
				$controller->message("<span class=\"error\"><b>Место занято</b></span>", 'Ошибка', "?set=fleet", 2);

			if ($TargetPlanet['ally_deposit'] == 0 && $HeDBRec['id'] != $controller->user->id && $fleetmission == 5)
				$controller->message("<span class=\"error\"><b>На планете нет склада альянса!</b></span>", 'Ошибка', "?set=fleet", 2);

			if ($fleetmission == 5)
			{
				$friend = $controller->db->query("SELECT id FROM game_buddy WHERE ((sender = " . $controller->user->id . " AND owner = " . $HeDBRec['id'] . ") OR (owner = " . $controller->user->id . " AND sender = " . $HeDBRec['id'] . ")) AND active = 1 LIMIT 1")->fetch();

				if ($HeDBRec['ally_id'] != $controller->user->ally_id && !isset($friend['id']) && (!isset($ad['id']) || (isset($ad['id']) && $ad['type'] != 2)))
					$controller->message("<span class=\"error\"><b>Нельзя охранять вражеские планеты!</b></span>", 'Ошибка', "?set=fleet", 2);
			}

			if ($TargetPlanet['id_owner'] == $controller->user->id && ($fleetmission == 1 || $fleetmission == 2))
				$controller->message("<span class=\"error\"><b>Невозможно атаковать самого себя!</b></span>", 'Ошибка', "?set=fleet", 2);

			if ($TargetPlanet['id_owner'] == $controller->user->id && $fleetmission == 6)
				$controller->message("<span class=\"error\"><b>Невозможно шпионить самого себя!</b></span>", 'Ошибка', "?set=fleet", 2);

			if (!$YourPlanet && $fleetmission == 4)
				$controller->message("<span class=\"error\"><b>Выполнение данной миссии невозможно!</b></span>", 'Ошибка', "?set=fleet", 2);
		}

		$speedPossible = array(10, 9, 8, 7, 6, 5, 4, 3, 2, 1);

		$maxFleetSpeed 		= min(Fleet::GetFleetMaxSpeed($fleetarray, 0, $controller->user));
		$fleetSpeedFactor 	= $controller->request->getPost('speed', 'int', 10);
		$gameFleetSpeed 	= $controller->game->getSpeed('fleet');

		if (!in_array($fleetSpeedFactor, $speedPossible))
			$controller->message("<span class=\"error\"><b>Читеришь со скоростью?</b></span>", 'Ошибка', "?set=fleet", 2);

		if (!$planettype)
			$controller->message("<span class=\"error\"><b>Ошибочный тип планеты!</b></span>", 'Ошибка', "?set=fleet", 2);

		$errorlist = "";

		if (!$galaxy || $galaxy > $controller->config->game->maxGalaxyInWorld || $galaxy < 1)
			$errorlist .= _getText('fl_limit_galaxy');

		if (!$system || $system > $controller->config->game->maxSystemInGalaxy || $system < 1)
			$errorlist .= _getText('fl_limit_system');

		if (!$planet || $planet > ($controller->config->game->maxPlanetInSystem + 1) || $planet < 1)
			$errorlist .= _getText('fl_limit_planet');

		if ($errorlist != '')
			$controller->message("<span class=\"error\">" . $errorlist . "</span>", 'Ошибка', "?set=fleet", 2);

		if (!isset($fleetarray))
			$controller->message("<span class=\"error\"><b>" . _getText('fl_no_fleetarray') . "</b></span>", 'Ошибка', "?set=fleet", 2);

		$distance 		= Fleet::GetTargetDistance($controller->planet->galaxy, $galaxy, $controller->planet->system, $system, $controller->planet->planet, $planet);
		$duration 		= Fleet::GetMissionDuration($fleetSpeedFactor, $maxFleetSpeed, $distance, $gameFleetSpeed);
		$consumption 	= Fleet::GetFleetConsumption($fleetarray, $gameFleetSpeed, $duration, $distance, $controller->user);

		$fleet_group_time = 0;

		$i = 0;

		if ($fleet_group_mr > 0)
		{
			// Вычисляем время самого медленного флота в совместной атаке
			$flet = $controller->db->query("SELECT fleet_id, fleet_start_time, fleet_end_time FROM game_fleets WHERE fleet_group = '" . $fleet_group_mr . "'");
			$fleet_group_time = $duration + time();
			$arrr = array();

			while ($flt = $flet->fetch())
			{
				$i++;

				if ($flt['fleet_start_time'] > $fleet_group_time)
					$fleet_group_time = $flt['fleet_start_time'];

				$arrr[$i]['id'] = $flt['fleet_id'];
				$arrr[$i]['start'] = $flt['fleet_start_time'];
				$arrr[$i]['end'] = $flt['fleet_end_time'];
			}
		}

		if ($fleet_group_mr > 0)
			$fleet['start_time'] = $fleet_group_time;
		else
			$fleet['start_time'] = $duration + time();

		if ($fleetmission == 15)
		{
			$StayDuration = intval($_POST['expeditiontime']) * 3600;
			$StayTime = $fleet['start_time'] + intval($_POST['expeditiontime']) * 3600;
		}
		else
		{
			$StayDuration = 0;
			$StayTime = 0;
		}

		$FleetStorage = 0;
		$fleet_array = "";

		$fleetPlanetUpdate = array();

		foreach ($fleetarray as $Ship => $Count)
		{
			$Count = intval($Count);

			if (isset($controller->user->{'fleet_' . $Ship}) && isset($controller->game->CombatCaps[$Ship]['power_consumption']) && $controller->game->CombatCaps[$Ship]['power_consumption'] > 0)
				$FleetStorage += round($controller->game->CombatCaps[$Ship]['capacity'] * (1 + $controller->user->{'fleet_' . $Ship} * ($controller->game->CombatCaps[$Ship]['power_consumption'] / 100))) * $Count;
			else
				$FleetStorage += $controller->game->CombatCaps[$Ship]['capacity'] * $Count;

			$fleet_array .= (isset($controller->user->{'fleet_' . $Ship})) ? $Ship . "," . $Count . "!" . $controller->user->{'fleet_' . $Ship} . ";" : $Ship . "," . $Count . "!0;";

			$fleetPlanetUpdate['-'.$controller->game->resource[$Ship]] = $Count;
		}

		$FleetStorage -= $consumption;
		$StorageNeeded = 0;

		if ($_POST['resource1'] < 1)
			$TransMetal = 0;
		else
		{
			$TransMetal = intval($_POST['resource1']);
			$StorageNeeded += $TransMetal;
		}

		if ($_POST['resource2'] < 1)
			$TransCrystal = 0;
		else
		{
			$TransCrystal = intval($_POST['resource2']);
			$StorageNeeded += $TransCrystal;
		}

		if ($_POST['resource3'] < 1)
			$TransDeuterium = 0;
		else
		{
			$TransDeuterium = intval($_POST['resource3']);
			$StorageNeeded += $TransDeuterium;
		}

		$TotalFleetCons = 0;

		if ($fleetmission == 5)
		{
			$StayArrayTime = array(0, 1, 2, 4, 8, 16, 32);

			if (!isset($_POST['holdingtime']) || !in_array($_POST['holdingtime'], $StayArrayTime))
				$_POST['holdingtime'] = 0;

			$FleetStayConsumption = Fleet::GetFleetStay($fleetarray, $controller->getDI());

			if ($controller->user->rpg_meta > time())
				$FleetStayConsumption = ceil($FleetStayConsumption * 0.9);

			$FleetStayAll = $FleetStayConsumption * intval($_POST['holdingtime']);

			if ($FleetStayAll >= ($controller->planet->deuterium - $TransDeuterium))
				$TotalFleetCons = $controller->planet->deuterium - $TransDeuterium;
			else
				$TotalFleetCons = $FleetStayAll;

			if ($FleetStorage < $TotalFleetCons)
				$TotalFleetCons = $FleetStorage;

			$FleetStayTime = round(($TotalFleetCons / $FleetStayConsumption) * 3600);

			$StayDuration = $FleetStayTime;
			$StayTime = $fleet['start_time'] + $FleetStayTime;
		}

		if ($fleet_group_mr > 0)
			$fleet['end_time'] = $StayDuration + $duration + $fleet_group_time;
		else
			$fleet['end_time'] = $StayDuration + (2 * $duration) + time();

		$StockMetal 	= $controller->planet->metal;
		$StockCrystal 	= $controller->planet->crystal;
		$StockDeuterium = $controller->planet->deuterium - ($consumption + $TotalFleetCons);

		$StockOk = ($StockMetal >= $TransMetal && $StockCrystal >= $TransCrystal && $StockDeuterium >= $TransDeuterium);

		if (!$StockOk && $TargetPlanet['id_owner'] != 1)
			$controller->message("<span class=\"error\"><b>" . _getText('fl_noressources') . Helpers::pretty_number($consumption) . "</b></span>", 'Ошибка', "?set=fleet", 2);

		if ($StorageNeeded > $FleetStorage && !$controller->user->isAdmin())
			$controller->message("<span class=\"error\"><b>" . _getText('fl_nostoragespa') . Helpers::pretty_number($StorageNeeded - $FleetStorage) . "</b></span>", 'Ошибка', "?set=fleet", 2);

		// Баш контроль
		if ($fleetmission == 1)
		{
			$night_time = mktime(0, 0, 0, date('m', time()), date('d', time()), date('Y', time()));

			$log = $controller->db->query("SELECT kolvo FROM game_logs WHERE `s_id` = '".$controller->user->id."' AND `mission` = 1 AND e_galaxy = " . $TargetPlanet['galaxy'] . " AND e_system = " . $TargetPlanet['system'] . " AND e_planet = " . $TargetPlanet['planet'] . " AND time > " . $night_time . "")->fetch();

			if (!$controller->user->isAdmin() && isset($log['kolvo']) && $log['kolvo'] > 2 && ((isset($ad['id']) && $ad['type'] != 3) || !isset($ad['id'])))
				$controller->message("<span class=\"error\"><b>Баш-контроль. Лимит ваших нападений на планету исчерпан.</b></span>", 'Ошибка', "?set=fleet", 2);

			if (isset($log['kolvo']))
				$controller->db->query("UPDATE game_logs SET kolvo = kolvo + 1 WHERE `s_id` = '".$controller->user->id."' AND `mission` = 1 AND e_galaxy = " . $TargetPlanet['galaxy'] . " AND e_system = " . $TargetPlanet['system'] . " AND e_planet = " . $TargetPlanet['planet'] . " AND time > " . $night_time . "");
			else
				$controller->db->query("INSERT INTO game_logs VALUES (1, " . time() . ", 1, " . $controller->user->id . ", " . $controller->planet->galaxy . ", " . $controller->planet->system . ", " . $controller->planet->planet . ", " . $TargetPlanet['id_owner'] . ", " . $TargetPlanet['galaxy'] . ", " . $TargetPlanet['system'] . ", " . $TargetPlanet['planet'] . ")");

		}
		//

		// Увод флота
		//$fleets_num = $this->db->query("SELECT fleet_id FROM game_fleets WHERE fleet_mission = '1' AND fleet_end_galaxy = ".$controller->planet->data['galaxy']." AND fleet_end_system = ".$controller->planet->data['system']." AND fleet_end_planet = ".$controller->planet->data['planet']." AND fleet_end_type = ".$controller->planet->data['planet_type']." AND fleet_start_time < ".(time() + 5)."");

		//if (db::num_rows($fleets_num) > 0)
		//		message ("<span class=\"error\"><b>Ваш флот не может взлететь из-за находящегося поблизости от орбиты планеты атакующего флота.</b></span>", 'Ошибка', "fleet." . $phpEx, 2);
		//

		if ($fleet_group_mr > 0 && $i > 0 && $fleet_group_time > 0 && isset($arrr))
		{
			foreach ($arrr AS $id => $row)
			{
				$end = $fleet_group_time + $row['end'] - $row['start'];
				$controller->db->query("UPDATE game_fleets SET fleet_start_time = " . $fleet_group_time . ", fleet_end_time = " . $end . ", fleet_time = " . $fleet_group_time . " WHERE fleet_id = '" . $row['id'] . "'");
			}
		}

		if (($fleetmission == 1 || $fleetmission == 2 || $fleetmission == 3) && $HeDBRec['id'] != $controller->user->id && !$controller->user->isAdmin())
		{
			$check = $controller->db->fetchColumn("SELECT COUNT(*) as num FROM game_log_ip WHERE id = ".$HeDBRec['id']." AND time > ".(time() - 86400 * 3)." AND ip IN (SELECT ip FROM game_log_ip WHERE id = ".$controller->user->id." AND time > ".(time() - 86400 * 3).")");

			if ($check > 0 || $HeDBRec['ip'] == $controller->user->ip)
				$controller->message("<span class=\"error\"><b>Вы не можете посылать флот с миссией \"Транспорт\" и \"Атака\" к игрокам, с которыми были пересечения по IP адресу.</b></span>", 'Ошибка', "?set=fleet", 5);
		}

		if ($fleetmission == 3 && $HeDBRec['id'] != $controller->user->id && !$controller->user->isAdmin())
		{
			if (isset($NoobNoActive) && $NoobNoActive == 1)
				$controller->message("<span class=\"error\"><b>Вы не можете посылать флот с миссией \"Транспорт\" к неактивному игроку.</b></span>", 'Ошибка', "?set=fleet", 5);

			$cnt = $controller->db->query("SELECT COUNT(*) as num FROM game_log_transfers WHERE user_id = ".$controller->user->id." AND target_id = ".$HeDBRec['id']." AND time > ".(time() - 86400 * 7)."");

			if ($cnt >= 3)
				$controller->message("<span class=\"error\"><b>Вы не можете посылать флот с миссией \"Транспорт\" другому игроку чаще 3х раз в неделю.</b></span>", 'Ошибка', "?set=fleet", 5);

			$cnt = $controller->db->query("SELECT COUNT(*) as num FROM game_log_transfers WHERE user_id = ".$controller->user->id." AND target_id = ".$HeDBRec['id']." AND time > ".(time() - 86400 * 1)."");

			if ($cnt > 0)
				$controller->message("<span class=\"error\"><b>Вы не можете посылать флот с миссией \"Транспорт\" другому игроку чаще одного раза в день.</b></span>", 'Ошибка', "?set=fleet", 5);

			//$equiv = $TransMetal + $TransCrystal * 2 + $TransDeuterium * 4;

			//if ($equiv > 15000000)
			//	$controller->message("<span class=\"error\"><b>Вы не можете посылать флот с миссией \"Транспорт\" другому игроку с количеством ресурсов большим чем 15кк в эквиваленте металла.</b></span>", 'Ошибка', "?set=fleet", 5);

			Sql::build()->insert('game_log_transfers')->set(Array
			(
				'time' 		=> time(),
				'user_id' 	=> $controller->user->id,
				'data' 		=> "s:[".$controller->planet->galaxy.":".$controller->planet->system.":".$controller->planet->planet."(".$controller->planet->planet_type.")];e:[".$galaxy.":".$system.":".$planet."(".$planettype.")];f:[".$fleet_array."];m:".$TransMetal.";c:".$TransCrystal.";d:".$TransDeuterium.";",
				'target_id' => $TargetPlanet['id_owner']
			))
			->execute();

			$str_error = "Информация о передаче ресурсов добавлена в журнал оператора.<br>";
		}

		if ($TargetPlanet['id_owner'] == 1)
		{
			$fleet['start_time'] = time() + 30;
			$fleet['end_time'] = time() + 60;
			$consumption = 0;
		}

		if ($controller->user->isAdmin() && $fleetmission != 6)
		{
			$fleet['start_time'] 	= time() + 15;
			$fleet['end_time'] 		= time() + 30;

			if ($StayTime)
				$StayTime = $fleet['start_time'] + 5;

			$consumption = 0;
		}

		$tutorial = $controller->db->query("SELECT id, quest_id FROM game_users_quests WHERE user_id = ".$controller->user->getId()." AND finish = '0' AND stage = 0")->fetch();

		if (isset($tutorial['id']))
		{
			Lang::includeLang('tutorial');

			$quest = _getText('tutorial', $tutorial['quest_id']);

			foreach ($quest['TASK'] AS $taskKey => $taskVal)
			{
				if ($taskKey == 'FLEET_MISSION' && $taskVal == $fleetmission)
				{
					$controller->db->query("UPDATE game_users_quests SET stage = 1 WHERE id = " . $tutorial['id'] . ";");
				}
			}
		}

		if ($fleetmission == 1)
		{
			$raunds = (isset($_POST['raunds'])) ? intval($_POST['raunds']) : 6;
			$raunds = ($raunds < 6 || $raunds > 10) ? 6 : $raunds;
		}
		else
			$raunds = 0;

		$controller->db->insertAsDict('game_fleets',
		[
			'fleet_owner' 			=> $controller->user->id,
			'fleet_owner_name' 		=> $controller->planet->name,
			'fleet_mission' 		=> $fleetmission,
			'fleet_array' 			=> $fleet_array,
			'fleet_start_time' 		=> $fleet['start_time'],
			'fleet_start_galaxy' 	=> $controller->planet->galaxy,
			'fleet_start_system' 	=> $controller->planet->system,
			'fleet_start_planet' 	=> $controller->planet->planet,
			'fleet_start_type' 		=> $controller->planet->planet_type,
			'fleet_end_time' 		=> $fleet['end_time'],
			'fleet_end_stay' 		=> $StayTime,
			'fleet_end_galaxy' 		=> $galaxy,
			'fleet_end_system' 		=> $system,
			'fleet_end_planet' 		=> $planet,
			'fleet_end_type' 		=> $planettype,
			'fleet_resource_metal' 	=> $TransMetal,
			'fleet_resource_crystal' 	=> $TransCrystal,
			'fleet_resource_deuterium' 	=> $TransDeuterium,
			'fleet_target_owner' 	=> $TargetPlanet['id_owner'],
			'fleet_target_owner_name' 	=> $TargetPlanet['name'],
			'fleet_group' 			=> $fleet_group_mr,
			'raunds' 				=> $raunds,
			'start_time' 			=> time(),
			'fleet_time' 			=> $fleet['start_time']
		]);

		$controller->planet->metal 		-= $TransMetal;
		$controller->planet->crystal 	-= $TransCrystal;
		$controller->planet->deuterium 	-= $TransDeuterium + $consumption + $TotalFleetCons;

		$fleetPlanetUpdate['metal'] 	= $controller->planet->metal;
		$fleetPlanetUpdate['crystal'] 	= $controller->planet->crystal;
		$fleetPlanetUpdate['deuterium'] = $controller->planet->deuterium;

		Sql::build()->update('game_planets')->set($fleetPlanetUpdate)->where('id', '=', $controller->planet->id)->execute();

		$html = "<center>";
		$html .= "<table border=\"0\" cellpadding=\"0\" cellspacing=\"1\" width=\"600\">";
		$html .= "<tr>";
		$html .= "<td class=\"c\" colspan=\"2\"><span class=\"success\">" . ((isset($str_error)) ? $str_error : _getText('fl_fleet_send')) . "</span></td>";
		$html .= "</tr><tr>";
		$html .= "<th>" . _getText('fl_mission') . "</th>";
		$html .= "<th>" . _getText('type_mission', $fleetmission) . "</th>";
		$html .= "</tr><tr>";
		$html .= "<th>" . _getText('fl_dist') . "</th>";
		$html .= "<th>" . Helpers::pretty_number($distance) . "</th>";
		$html .= "</tr><tr>";
		$html .= "<th>" . _getText('fl_speed') . "</th>";
		$html .= "<th>" . Helpers::pretty_number($maxFleetSpeed) . "</th>";
		$html .= "</tr><tr>";
		$html .= "<th>" . _getText('fl_deute_need') . "</th>";
		$html .= "<th>" . Helpers::pretty_number($consumption) . "</th>";
		$html .= "</tr><tr>";
		$html .= "<th>" . _getText('fl_from') . "</th>";
		$html .= "<th>" . $controller->planet->galaxy . ":" . $controller->planet->system . ":" . $controller->planet->planet . "</th>";
		$html .= "</tr><tr>";
		$html .= "<th>" . _getText('fl_dest') . "</th>";
		$html .= "<th>" . $galaxy . ":" . $system . ":" . $planet . "</th>";
		$html .= "</tr><tr>";
		$html .= "<th>" . _getText('fl_time_go') . "</th>";
		$html .= "<th>" . $controller->game->datezone("d H:i:s", $fleet['start_time']) . "</th>";
		$html .= "</tr><tr>";
		$html .= "<th>" . _getText('fl_time_back') . "</th>";
		$html .= "<th>" . $controller->game->datezone("d H:i:s", $fleet['end_time']) . "</th>";
		$html .= "</tr><tr>";
		$html .= "<td class=\"c\" colspan=\"2\">" . _getText('fl_title') . "</td>";


		foreach ($fleetarray as $Ship => $Count)
		{
			$html .= "</tr><tr>";
			$html .= "<th>" . _getText('tech', $Ship) . "</th>";
			$html .= "<th>" . Helpers::pretty_number($Count) . "</th>";
		}
		$html .= "</tr></table></center>";

		$controller->message($html, '' . _getText('fl_title_3') . '', '?set=fleet', '3');
	}
}

?>