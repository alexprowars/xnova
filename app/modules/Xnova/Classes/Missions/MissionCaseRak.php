<?php

namespace Xnova\Missions;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\FleetEngine;
use Xnova\Models\Planet;
use Xnova\User;
use Xnova\Vars;

class MissionCaseRak extends FleetEngine implements Mission
{
	public function TargetEvent()
	{
		$this->KillFleet();

		$targetPlanet = Planet::findByCoords($this->_fleet->end_galaxy, $this->_fleet->end_system, $this->_fleet->end_planet, 1);

		if (!$targetPlanet)
			return;

		$defTech = $this->db->query('SELECT * FROM game_users_tech WHERE user_id = ?0 AND tech_id = ?1',
				[$this->_fleet->target_owner, Vars::getIdByName('defence')])->fetch();

		if (!$defTech)
			$defTech['level'] = 0;

		$attTech = $this->db->query('SELECT * FROM game_users_tech WHERE user_id = ?0 AND tech_id = ?1',
				[$this->_fleet->owner, Vars::getIdByName('military')])->fetch();

		if (!$attTech)
			$attTech['level'] = 0;

		$message = '';

		$Raks = 0;
		$Primary = 401;

		$fleetData = $this->_fleet->getShips();

		foreach ($fleetData as $shipId => $shipArr)
		{
			if ($shipId != 503)
				continue;

			$Raks = $shipArr['cnt'];
			$Primary = $shipArr['lvl'];
		}

		$TargetDefensive = [];

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_DEFENSE) as $Element)
			$TargetDefensive[$Element] = $targetPlanet->getUnitCount($Element);

		$defenceMissiles = $targetPlanet->getUnitCount('interceptor_misil');

		if ($defenceMissiles >= $Raks)
		{
			$message .= 'Вражеская ракетная атака была отбита ракетами-перехватчиками<br>';

			$targetPlanet->setUnit('interceptor_misil', -$Raks, true);
		}
		else
		{
			$message .= 'Произведена межпланетная атака (' . $Raks . ' ракет) с ' . $this->_fleet->owner_name . ' <a href="#PATH#galaxy/' . $this->_fleet->start_galaxy . '/' . $this->_fleet->start_system . '/">[' . $this->_fleet->start_galaxy . ':' . $this->_fleet->start_system . ':' . $this->_fleet->start_planet . ']</a>';
			$message .= ' на планету ' . $this->_fleet->target_owner_name . ' <a href="#PATH#galaxy/' . $this->_fleet->end_galaxy . '/' . $this->_fleet->end_system . '/">[' . $this->_fleet->end_galaxy . ':' . $this->_fleet->end_system . ':' . $this->_fleet->end_planet . ']</a>.<br><br>';

			if ($defenceMissiles > 0)
			{
				$message .= $defenceMissiles." ракеты-перехватчика частично отбили атаку вражеских межпланетных ракет.<br>";

				$targetPlanet->setUnit('interceptor_misil', 0);
			}

			$Raks -= $defenceMissiles;

			$irak = $this->raketenangriff($defTech['level'], $attTech['level'], $Raks, $TargetDefensive, $Primary);

			ksort($irak, SORT_NUMERIC);

			foreach ($irak as $Element => $destroy)
			{
				if (empty($Element) || $destroy == 0)
					continue;

				$message .= _getText('tech', $Element) . " (" . $destroy . " уничтожено)<br>";

				$targetPlanet->setUnit($Element, -$destroy, true);
			}
		}

		$targetPlanet->update();

		if (empty($message))
			$message = "Нет обороны для разрушения!";

		User::sendMessage($this->_fleet->target_owner, 0, $this->_fleet->start_time, 3, 'Ракетная атака', $message);
	}

	public function EndStayEvent()
	{
		return;
	}

	public function ReturnEvent()
	{
		return;
	}

	private function raketenangriff ($TargetDefTech, $OwnerAttTech, $ipm, $TargetDefensive, $pri_target = 0)
	{
		unset($TargetDefensive[502]);

		$life_fac = $TargetDefTech / 10 + 1;
		$life_fac_a = $this->registry->CombatCaps[503]['attack'] * ($OwnerAttTech / 10 + 1);

		$max_dam = $ipm * $life_fac_a;
		$i = 0;

		$ship_res = [];

		foreach ($TargetDefensive as $Element => $Count)
		{
			if ($i == 0)
				$target = $pri_target;
			elseif ($Element <= $pri_target)
				$target = $Element - 1;
			else
				$target = $Element;

			$price = Vars::getItemTotalPrice($target);

			$Dam = $max_dam - $price / 10 * $TargetDefensive[$target] * $life_fac;

			if ($Dam > 0)
			{
				$dest = $TargetDefensive[$target];
				$ship_res[$target] = $dest;
			}
			else
			{
				$dest = floor($max_dam / ($price / 10 * $life_fac));
				$ship_res[$target] = $dest;
			}
			$max_dam -= $dest * round($price / 10 * $life_fac);
			$i++;
		}

		return $ship_res;
	}
}