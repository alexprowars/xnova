<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Coordinates;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\MessageType;
use App\Engine\Enums\PlanetType;
use App\Engine\Messages\Types\MissionMissileAttackMessage;
use App\Engine\Objects\DefenceObject;
use App\Engine\Objects\ObjectsFactory;
use App\Facades\Vars;
use App\Models\Planet;
use App\Notifications\SystemMessage;

class MissileAttack extends BaseMission
{
	public static function isMissionPossible(Planet $planet, Coordinates $target, ?Planet $targetPlanet, array $units = [], bool $isAssault = false): bool
	{
		return false;
	}

	public function targetEvent(): void
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

		$message = [];

		if ($defenceMissiles >= $rockets) {
			$targetPlanet->updateAmount('interceptor_misil', -$rockets, true);
		} else {
			$message = [
				'missiles' => $rockets,
				'missiles_destroyed' => $defenceMissiles,
				'planet' => [
					'name' => $this->fleet->user_name,
					...$this->fleet->getOriginCoordinates(false)->toArray(),
				],
				'target' => [
					'name' => $this->fleet->target_user_name,
					...$this->fleet->getDestinationCoordinates(false)->toArray(),
				],
				'destroyed' => [],
			];

			if ($defenceMissiles > 0) {
				$targetPlanet->updateAmount('interceptor_misil', 0);
			}

			$rockets -= $defenceMissiles;

			$irak = $this->raketenangriff($defTech->level, $attTech->level, $rockets, $targetDefensive, $targetType);

			ksort($irak, SORT_NUMERIC);

			foreach ($irak as $elementId => $destroy) {
				if (empty($elementId) || $destroy == 0) {
					continue;
				}

				$message['destroyed'][$elementId] = $destroy;

				$targetPlanet->updateAmount($elementId, -$destroy, true);
			}
		}

		$targetPlanet->update();

		$this->fleet->target->notify(
			new SystemMessage(MessageType::Battle, new MissionMissileAttackMessage($message))
		);
	}

	public function returnEvent(): void
	{
	}

	private function raketenangriff(int $targetDefTech, int $ownerAttTech, int $missiles, array $targetDefensive, ?int $firstTarget = null): array
	{
		if (!$missiles) {
			return [];
		}

		unset($targetDefensive[502]);

		/** @var DefenceObject $fleetObject */
		$fleetObject = ObjectsFactory::get(503);

		$totalDamage = $missiles * $fleetObject->getAttack() * ($ownerAttTech / 10 + 1);

		$result = [];

		if ($firstTarget && isset($targetDefensive[$firstTarget])) {
			$c = $targetDefensive[$firstTarget];

			unset($targetDefensive[$firstTarget]);
			$targetDefensive = [$firstTarget => $c] + $targetDefensive;
		}

		foreach ($targetDefensive as $target => $count) {
			if (!$target) {
				continue;
			}

			/** @var DefenceObject $targetObject */
			$targetObject = ObjectsFactory::get($target);

			$structure = ($targetObject->getTotalPrice() / 10 * ($targetDefTech / 10 + 1));

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
