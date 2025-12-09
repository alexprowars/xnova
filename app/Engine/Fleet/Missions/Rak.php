<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Coordinates;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\MessageType;
use App\Engine\Enums\PlanetType;
use App\Facades\Vars;
use App\Models\Planet;
use App\Notifications\MessageNotification;

class Rak extends BaseMission
{
	public static function isMissionPossible(Planet $planet, Coordinates $target, ?Planet $targetPlanet, array $units = [], bool $isAssault = false): bool
	{
		return false;
	}

	public function targetEvent()
	{
		$this->killFleet();

		$targetPlanet = Planet::findByCoordinates(new Coordinates($this->fleet->end_galaxy, $this->fleet->end_system, $this->fleet->end_planet, PlanetType::PLANET));

		if (!$targetPlanet) {
			return;
		}

		$attTech = $this->fleet->user->getTech('defence');
		$defTech = $this->fleet->target->getTech('military');

		$fleetEntity = $this->fleet->entities->getByEntityId(503);

		$rockets = $fleetEntity->count ?? 0;
		$targetType = $fleetEntity->getParam('target') ?? 0;

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

			if (empty($irak)) {
				$message .= 'Нет обороны для разрушения!';
			}
		}

		$targetPlanet->update();

		$this->fleet->target->notify(new MessageNotification(null, MessageType::Battle, 'Ракетная атака', $message));
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

			if ($destroyCount <= 0) {
				continue;
			}

			$result[$target] = $destroyCount;

			$totalDamage -= $destroyCount * $structure;

			if ($totalDamage <= 0) {
				break;
			}
		}

		return $result;
	}
}
