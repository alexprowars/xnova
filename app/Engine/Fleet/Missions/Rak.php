<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Coordinates;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\MessageType;
use App\Engine\Enums\PlanetType;
use App\Engine\Fleet\FleetEngine;
use App\Engine\Vars;
use App\Models\Planet;
use App\Models\UserTech;
use App\Notifications\MessageNotification;

class Rak extends FleetEngine implements Mission
{
	public function targetEvent()
	{
		$this->killFleet();

		$targetPlanet = Planet::findByCoordinates(new Coordinates($this->fleet->end_galaxy, $this->fleet->end_system, $this->fleet->end_planet, PlanetType::PLANET));

		if (!$targetPlanet) {
			return;
		}

		$defTech = UserTech::query()->where('user_id', $this->fleet->target_user_id)
			->where('tech_id', Vars::getIdByName('defence_tech'))
			->firstOrNew();

		$attTech = UserTech::query()->where('user_id', $this->fleet->user_id)
			->where('tech_id', Vars::getIdByName('military_tech'))
			->firstOrNew();

		$fleetData = $this->fleet->getShips();

		$rockets = $fleetData[503]['count'] ?? 0;
		$targetType = $fleetData[503]['target'] ?? 0;

		$targetDefensive = [];

		foreach (Vars::getItemsByType(ItemType::DEFENSE) as $elementId) {
			$targetDefensive[$elementId] = $targetPlanet->getLevel($elementId);
		}

		$defenceMissiles = $targetPlanet->getLevel('interceptor_misil');

		$message = '';

		if ($defenceMissiles >= $rockets) {
			$message .= 'Вражеская ракетная атака была отбита ракетами-перехватчиками<br>';

			$targetPlanet->updateAmount('interceptor_misil', -$rockets, true);
		} else {
			$message .= 'Произведена межпланетная атака (' . $rockets . ' ракет) с ' . $this->fleet->user_name . ' <a href="/galaxy/?galaxy=' . $this->fleet->start_galaxy . '&system=' . $this->fleet->start_system . '">[' . $this->fleet->start_galaxy . ':' . $this->fleet->start_system . ':' . $this->fleet->start_planet . ']</a>';
			$message .= ' на планету ' . $this->fleet->target_user_name . ' <a href="/galaxy/?galaxy=' . $this->fleet->end_galaxy . '&system=' . $this->fleet->end_system . '">[' . $this->fleet->end_galaxy . ':' . $this->fleet->end_system . ':' . $this->fleet->end_planet . ']</a>.<br><br>';

			if ($defenceMissiles > 0) {
				$message .= $defenceMissiles . ' ракеты-перехватчика частично отбили атаку вражеских межпланетных ракет.<br>';

				$targetPlanet->updateAmount('interceptor_misil', 0);
			}

			$rockets -= $defenceMissiles;

			$irak = $this->raketenangriff($defTech->level, $attTech->level, $rockets, $targetDefensive, $targetType);

			ksort($irak, SORT_NUMERIC);

			foreach ($irak as $elementId => $destroy) {
				if (empty($elementId) || $destroy == 0) {
					continue;
				}

				$message .= __('main.tech.' . $elementId) . ' (' . $destroy . ' уничтожено)<br>';

				$targetPlanet->updateAmount($elementId, -$destroy, true);
			}
		}

		$targetPlanet->update();

		if (empty($message)) {
			$message = 'Нет обороны для разрушения!';
		}

		$this->fleet->target->notify(new MessageNotification(null, MessageType::Battle, 'Ракетная атака', $message));
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
