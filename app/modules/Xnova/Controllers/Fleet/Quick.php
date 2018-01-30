<?php

namespace Xnova\Controllers\Fleet;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Friday\Core\Options;
use Xnova\Controllers\FleetController;
use Xnova\Fleet;
use Friday\Core\Lang;
use Xnova\Models\Planet;

class Quick
{
	public function show (FleetController $controller)
	{
		if ($controller->user->vacation > 0)
			throw new \Exception('Нет доступа!');

		Lang::includeLang('fleet', 'xnova');

		$maxfleet = \Xnova\Models\Fleet::count(['owner = ?0', 'bind' => [$controller->user->id]]);

		$MaxFlottes = 1 + $controller->user->getTechLevel('computer');

		if ($controller->user->rpg_admiral > time())
			$MaxFlottes += 2;

		$Mode 	= $controller->request->getQuery('mode', 'int', 0);
		$Galaxy = $controller->request->getQuery('g', 'int', 0);
		$System = $controller->request->getQuery('s', 'int', 0);
		$Planet = $controller->request->getQuery('p', 'int', 0);
		$TypePl = $controller->request->getQuery('t', 'int', 0);
		$num 	= $controller->request->getQuery('count', 'int', 0);

		if ($MaxFlottes <= $maxfleet)
			throw new \Exception('Все слоты флота заняты');
		elseif ($Galaxy > $controller->config->game->maxGalaxyInWorld || $Galaxy < 1)
			throw new \Exception('Ошибочная галактика!');
		elseif ($System > $controller->config->game->maxSystemInGalaxy || $System < 1)
			throw new \Exception('Ошибочная система!');
		elseif ($Planet > $controller->config->game->maxPlanetInSystem || $Planet < 1)
			throw new \Exception('Ошибочная планета!');
		elseif ($TypePl != 1 && $TypePl != 2 && $TypePl != 3 && $TypePl != 5)
			throw new \Exception('Ошибочный тип планеты!');

		if ($controller->planet->galaxy == $Galaxy && $controller->planet->system == $System && $controller->planet->planet == $Planet && $controller->planet->planet_type == $TypePl)
			$target = $controller->planet;
		else
			$target = Planet::findFirst(['galaxy = ?0 AND system = ?1 AND planet = ?2 AND (planet_type = '.($TypePl == 2 ? '1 OR planet_type = 5' : $TypePl).')', 'bind' => [$Galaxy, $System, $Planet]]);

		if (!$target)
			throw new \Exception('Цели не существует!');

		if (($Mode == 1 || $Mode == 6 || $Mode == 9 || $Mode == 2) && Options::get('disableAttacks', 0) > 0 && time() < Options::get('disableAttacks', 0))
			throw new \Exception("<span class=\"error\"><b>Посылать флот в атаку временно запрещено.<br>Дата включения атак " . $controller->game->datezone("d.m.Y H ч. i мин.", Options::get('disableAttacks', 0)) . "</b></span>");

		$FleetArray = [];

		if ($Mode == 6 && ($TypePl == 1 || $TypePl == 3 || $TypePl == 5))
		{
			if ($num <= 0)
				throw new \Exception('Вы были забанены за читерство!');
			if ($controller->planet->getUnitCount('spy_sonde') == 0)
				throw new \Exception('Нет шпионских зондов ля отправки!');
			if ($target->id_owner == $controller->user->id)
				throw new \Exception('Невозможно выполнить задание!');

			$HeDBRec = $controller->db->query("SELECT id, onlinetime, vacation FROM game_users WHERE id = '" . $target->id_owner . "';")->fetch();

			$UserPoints  = $controller->db->query("SELECT total_points FROM game_statpoints WHERE stat_type = '1' AND stat_code = '1' AND id_owner = '" . $controller->user->id . "';")->fetch();
			$User2Points = $controller->db->query("SELECT total_points FROM game_statpoints WHERE stat_type = '1' AND stat_code = '1' AND id_owner = '" . $HeDBRec['id'] . "';")->fetch();

			$MyGameLevel = $UserPoints['total_points'];
			$HeGameLevel = $User2Points['total_points'];

			if (!$HeGameLevel)
				$HeGameLevel = 0;

			if ($HeDBRec['onlinetime'] < (time() - 60 * 60 * 24 * 7))
				$NoobNoActive = 1;
			else
				$NoobNoActive = 0;

			if ($controller->user->authlevel != 3)
			{
				if ($NoobNoActive == 0 AND $HeGameLevel < ($controller->config->game->get('noobprotectiontime') * 1000))
				{
					if ($MyGameLevel > ($HeGameLevel * $controller->config->game->get('noobprotectionmulti')))
						throw new \Exception('Игрок находится под защитой новичков!');
					if (($MyGameLevel * $controller->config->game->get('noobprotectionmulti')) < $HeGameLevel)
						throw new \Exception('Вы слишком слабы для нападения на этого игрока!');
				}
			}

			if ($HeDBRec['vacation'] > 0)
				throw new \Exception('Игрок в режиме отпуска!');

			if ($controller->planet->getUnitCount('spy_sonde') < $num)
				$num = $controller->planet->getUnitCount('spy_sonde');

			$FleetArray[210] = $num;

			$FleetSpeed = min(Fleet::GetFleetMaxSpeed($FleetArray, 0, $controller->user));

		}
		elseif ($Mode == 8 && $TypePl == 2)
		{
			$DebrisSize = $target->debris_metal + $target->debris_crystal;

			if ($DebrisSize == 0)
				throw new \Exception('Нет обломков для сбора!');
			if ($controller->planet->getUnitCount('recycler') == 0)
				throw new \Exception('Нет переработчиков для сбора обломков!');

			$RecyclerNeeded = 0;

			if ($controller->planet->getUnitCount('recycler') > 0 && $DebrisSize > 0)
			{
				$RecyclerNeeded = floor($DebrisSize / ($controller->registry->CombatCaps[209]['capacity'] * (1 + $controller->user->getTechLevel('fleet_209') * ($controller->registry->CombatCaps[209]['power_consumption'] / 100)))) + 1;

				if ($RecyclerNeeded > $controller->planet->getUnitCount('recycler'))
					$RecyclerNeeded = $controller->planet->getUnitCount('recycler');
			}

			if ($RecyclerNeeded > 0)
			{
				$FleetArray[209] = $RecyclerNeeded;

				$FleetSpeed = min(Fleet::GetFleetMaxSpeed($FleetArray, 0, $controller->user));
			}
			else
				throw new \Exception('Произошла какая-то непонятная ситуация');
		}
		else
			throw new \Exception('Такой миссии не существует!');

		if ($FleetSpeed > 0 && count($FleetArray) > 0)
		{
			$SpeedFactor = $controller->game->getSpeed('fleet');
			$distance = Fleet::GetTargetDistance($controller->planet->galaxy, $Galaxy, $controller->planet->system, $System, $controller->planet->planet, $Planet);
			$duration = Fleet::GetMissionDuration(10, $FleetSpeed, $distance, $SpeedFactor);

			$consumption = Fleet::GetFleetConsumption($FleetArray, $SpeedFactor, $duration, $distance, $controller->user);

			$ShipCount = 0;
			$ShipArray = '';
			$FleetStorage = 0;

			foreach ($FleetArray as $Ship => $Count)
			{
				$controller->planet->setUnit($Ship, -$Count, true);

				$ShipArray .=  $Ship . "," . $Count . "!" . (isset($controller->user->{'fleet_' . $Ship}) ? $controller->user->{'fleet_' . $Ship} : 0) . ";";
				$ShipCount += $Count;

				if (isset($controller->user->{'fleet_' . $Ship}) && isset($controller->registry->CombatCaps[$Ship]['power_consumption']) && $controller->registry->CombatCaps[$Ship]['power_consumption'] > 0)
					$FleetStorage += round($controller->registry->CombatCaps[$Ship]['capacity'] * (1 + $controller->user->{'fleet_' . $Ship} * ($controller->registry->CombatCaps[$Ship]['power_consumption'] / 100))) * $Count;
				else
					$FleetStorage += $controller->registry->CombatCaps[$Ship]['capacity'] * $Count;
			}

			if ($FleetStorage < $consumption)
				throw new \Exception('Не хватает места в трюме для топлива! (необходимо еще ' . ($consumption - $FleetStorage) . ')');
			if ($controller->planet->deuterium < $consumption)
				throw new \Exception('Не хватает топлива на полёт! (необходимо еще ' . ($consumption - $controller->planet->deuterium) . ')');

			if ($ShipCount > 0)
			{
				$fleet = new \Xnova\Models\Fleet();

				$fleet->owner = $controller->user->id;
				$fleet->owner_name = $controller->planet->name;
				$fleet->mission = $Mode;
				$fleet->fleet_array = $ShipArray;
				$fleet->start_time = $duration + time();
				$fleet->start_galaxy = $controller->planet->galaxy;
				$fleet->start_system = $controller->planet->system;
				$fleet->start_planet = $controller->planet->planet;
				$fleet->start_type = $controller->planet->planet_type;
				$fleet->end_time = ($duration * 2) + time();
				$fleet->end_galaxy = $Galaxy;
				$fleet->end_system = $System;
				$fleet->end_planet = $Planet;
				$fleet->end_type = $TypePl;
				$fleet->create_time = time();
				$fleet->update_time = $duration + time();

				if ($Mode == 6 && isset($HeDBRec['id']))
				{
					$fleet->target_owner = $HeDBRec['id'];
					$fleet->target_owner_name = $target->name;
				}

				if ($fleet->create())
				{
					$controller->planet->deuterium -= $consumption;
					$controller->planet->update();

					$tutorial = $controller->db->query("SELECT id, quest_id FROM game_users_quests WHERE user_id = ".$controller->user->getId()." AND finish = '0' AND stage = 0")->fetch();

					if (isset($tutorial['id']))
					{
						Lang::includeLang('tutorial', 'xnova');

						$quest = _getText('tutorial', $tutorial['quest_id']);

						foreach ($quest['TASK'] AS $taskKey => $taskVal)
						{
							if ($taskKey == 'FLEET_MISSION' && $taskVal == $Mode)
							{
								$controller->db->query("UPDATE game_users_quests SET stage = 1 WHERE id = " . $tutorial['id'] . ";");
							}
						}
					}

					return "Флот отправлен на координаты [" . $Galaxy . ":" . $System . ":" . $Planet . "] с миссией " . _getText('type_mission', $Mode) . " и прибудет к цели в " . $controller->game->datezone("H:i:s", ($duration + time()));
				}
			}
		}

		throw new \Exception('Произошла ошибка');
	}
}