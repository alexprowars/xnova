<?php

namespace Xnova\Missions;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\FleetEngine;
use Xnova\Planet;
use Xnova\User;
use Xnova\Vars;

class Rak extends FleetEngine implements Mission
{
	public function TargetEvent()
	{
		$this->KillFleet();

		$targetPlanet = Planet::findByCoords($this->_fleet->end_galaxy, $this->_fleet->end_system, $this->_fleet->end_planet, 1);

		if (!$targetPlanet)
			return;

		$defTech = $this->db->query('SELECT level FROM users_tech WHERE user_id = ? AND tech_id = ?',
				[$this->_fleet->target_owner, Vars::getIdByName('defence_tech')])->fetch();

		if (!$defTech)
			$defTech['level'] = 0;

		$attTech = $this->db->query('SELECT level FROM users_tech WHERE user_id = ? AND tech_id = ?',
				[$this->_fleet->owner, Vars::getIdByName('military_tech')])->fetch();

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

			$Raks = $shipArr['count'];
			$Primary = $shipArr['target'];
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
			$message .= 'Произведена межпланетная атака (' . $Raks . ' ракет) с ' . $this->_fleet->owner_name . ' <a href="/galaxy/?galaxy=' . $this->_fleet->start_galaxy . '&system=' . $this->_fleet->start_system . '">[' . $this->_fleet->start_galaxy . ':' . $this->_fleet->start_system . ':' . $this->_fleet->start_planet . ']</a>';
			$message .= ' на планету ' . $this->_fleet->target_owner_name . ' <a href="/galaxy/?galaxy=' . $this->_fleet->end_galaxy . '&system=' . $this->_fleet->end_system . '">[' . $this->_fleet->end_galaxy . ':' . $this->_fleet->end_system . ':' . $this->_fleet->end_planet . ']</a>.<br><br>';

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

				$message .= __('main.tech.'.$Element) . " (" . $destroy . " уничтожено)<br>";

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

	private function raketenangriff ($targetDefTech, $ownerAttTech, $missiles, $targetDefensive, $firstTarget = 0)
	{
		if (!$missiles)
			return [];

		unset($targetDefensive[502]);

		$totalDamage = $missiles * $this->registry->CombatCaps[503]['attack'] * ($ownerAttTech / 10 + 1);

		$result = [];

		if (isset($targetDefensive[$firstTarget]))
		{
			$c = $targetDefensive[$firstTarget];

			unset($targetDefensive[$firstTarget]);
			$targetDefensive = [$firstTarget => $c] + $targetDefensive;
		}

		foreach ($targetDefensive as $target => $count)
		{
			if (!$target)
				continue;

			$price = Vars::getItemTotalPrice($target);

			$structure = ($price / 10 * ($targetDefTech / 10 + 1));

			$destroyCount = floor($totalDamage / $structure);
			$destroyCount = min($destroyCount, $count);

			$result[$target] = $destroyCount;

			$totalDamage -= $destroyCount * $structure;

			if ($totalDamage <= 0)
				break;
		}

		return $result;
	}
}