<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Coordinates;
use App\Engine\FleetEngine;
use App\Engine\Vars;
use App\Models\Planet;
use App\Models\User;
use App\Models\UserTech;

class Rak extends FleetEngine implements Mission
{
	public function targetEvent()
	{
		$this->killFleet();

		$targetPlanet = Planet::findByCoordinates(new Coordinates($this->fleet->end_galaxy, $this->fleet->end_system, $this->fleet->end_planet, Coordinates::TYPE_PLANET));

		if (!$targetPlanet) {
			return;
		}

		$defTech = UserTech::query()->where('user_id', $this->fleet->target_user_id)
			->where('tech_id', Vars::getIdByName('defence_tech'))
			->firstOrNew();

		$attTech = UserTech::query()->where('user_id', $this->fleet->user_id)
			->where('tech_id', Vars::getIdByName('military_tech'))
			->firstOrNew();

		$message = '';

		$Raks = 0;
		$Primary = 401;

		$fleetData = $this->fleet->getShips();

		foreach ($fleetData as $shipId => $shipArr) {
			if ($shipId != 503) {
				continue;
			}

			$Raks = $shipArr['count'];
			$Primary = $shipArr['target'];
		}

		$TargetDefensive = [];

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_DEFENSE) as $Element) {
			$TargetDefensive[$Element] = $targetPlanet->getLevel($Element);
		}

		$defenceMissiles = $targetPlanet->getLevel('interceptor_misil');

		if ($defenceMissiles >= $Raks) {
			$message .= 'Вражеская ракетная атака была отбита ракетами-перехватчиками<br>';

			$targetPlanet->updateAmount('interceptor_misil', -$Raks, true);
		} else {
			$message .= 'Произведена межпланетная атака (' . $Raks . ' ракет) с ' . $this->fleet->user_name . ' <a href="/galaxy/?galaxy=' . $this->fleet->start_galaxy . '&system=' . $this->fleet->start_system . '">[' . $this->fleet->start_galaxy . ':' . $this->fleet->start_system . ':' . $this->fleet->start_planet . ']</a>';
			$message .= ' на планету ' . $this->fleet->target_user_name . ' <a href="/galaxy/?galaxy=' . $this->fleet->end_galaxy . '&system=' . $this->fleet->end_system . '">[' . $this->fleet->end_galaxy . ':' . $this->fleet->end_system . ':' . $this->fleet->end_planet . ']</a>.<br><br>';

			if ($defenceMissiles > 0) {
				$message .= $defenceMissiles . " ракеты-перехватчика частично отбили атаку вражеских межпланетных ракет.<br>";

				$targetPlanet->updateAmount('interceptor_misil', 0);
			}

			$Raks -= $defenceMissiles;

			$irak = $this->raketenangriff($defTech->level, $attTech->level, $Raks, $TargetDefensive, $Primary);

			ksort($irak, SORT_NUMERIC);

			foreach ($irak as $Element => $destroy) {
				if (empty($Element) || $destroy == 0) {
					continue;
				}

				$message .= __('main.tech.' . $Element) . " (" . $destroy . " уничтожено)<br>";

				$targetPlanet->updateAmount($Element, -$destroy, true);
			}
		}

		$targetPlanet->update();

		if (empty($message)) {
			$message = "Нет обороны для разрушения!";
		}

		User::sendMessage($this->fleet->target_user_id, 0, $this->fleet->start_time, 4, 'Ракетная атака', $message);
	}

	public function endStayEvent()
	{
	}

	public function returnEvent()
	{
	}

	private function raketenangriff($targetDefTech, $ownerAttTech, $missiles, $targetDefensive, $firstTarget = 0)
	{
		if (!$missiles) {
			return [];
		}

		unset($targetDefensive[502]);

		$fleetData = Vars::getUnitData(503);

		$totalDamage = $missiles * $fleetData['attack'] * ($ownerAttTech / 10 + 1);

		$result = [];

		if (isset($targetDefensive[$firstTarget])) {
			$c = $targetDefensive[$firstTarget];

			unset($targetDefensive[$firstTarget]);
			$targetDefensive = [$firstTarget => $c] + $targetDefensive;
		}

		foreach ($targetDefensive as $target => $count) {
			if (!$target) {
				continue;
			}

			$price = Vars::getItemTotalPrice($target);

			$structure = ($price / 10 * ($targetDefTech / 10 + 1));

			$destroyCount = floor($totalDamage / $structure);
			$destroyCount = min($destroyCount, $count);

			$result[$target] = $destroyCount;

			$totalDamage -= $destroyCount * $structure;

			if ($totalDamage <= 0) {
				break;
			}
		}

		return $result;
	}
}
