<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Coordinates;
use App\Engine\Enums\FleetDirection;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\MessageType;
use App\Engine\Messages\Types\MissionEspionageMessage;
use App\Engine\Messages\Types\MissionEspionageNotifyMessage;
use App\Engine\QueueManager;
use App\Facades\Vars;
use App\Models\Fleet;
use App\Models\Planet;
use App\Models\User;
use App\Notifications\SystemMessage;

class Espionage extends BaseMission
{
	public static function isMissionPossible(Planet $planet, Coordinates $target, ?Planet $targetPlanet, array $units = [], bool $isAssault = false): bool
	{
		return !empty($units[210]) && $targetPlanet && $planet->user_id != $targetPlanet->user_id;
	}

	public function targetEvent(): void
	{
		$owner = User::query()->find($this->fleet->user_id);

		$TargetPlanet = Planet::findByCoordinates($this->fleet->getDestinationCoordinates());

		if ($TargetPlanet->user_id == 0) {
			$this->return();
			return;
		}

		$targetUser = $TargetPlanet->user;

		if (!$TargetPlanet) {
			$this->return();
			return;
		}

		$TargetPlanet->getProduction($this->fleet->start_date)->update();

		$queueManager = new QueueManager($TargetPlanet);
		$queueManager->checkUnitQueue();

		$CurrentSpyLvl = $owner->getTechLevel('spy');

		if ($owner->officier_technocrat->isFuture()) {
			$CurrentSpyLvl += 2;
		}

		$TargetSpyLvl = $targetUser->getTechLevel('spy');

		if ($targetUser->officier_technocrat->isFuture()) {
			$TargetSpyLvl += 2;
		}

		$spySondeEntity = $this->fleet->entities->getByEntityId(210);

		if ($spySondeEntity && $spySondeEntity->count) {
			$defenders = Fleet::query()
				->coordinates(FleetDirection::END, $this->fleet->getDestinationCoordinates())
				->where('mess', 3)
				->get();

			foreach ($defenders as $row) {
				foreach ($row->entities as $entity) {
					if ($entity->id < 100) {
						continue;
					}

					$TargetPlanet->updateAmount($entity->id, $entity->count, true);
				}
			}

			$ST = 0;

			$techDifference = abs($CurrentSpyLvl - $TargetSpyLvl);

			if ($TargetSpyLvl > $CurrentSpyLvl) {
				$ST = ($spySondeEntity->count - ($techDifference ** 2));
			}
			if ($CurrentSpyLvl >= $TargetSpyLvl) {
				$ST = ($spySondeEntity->count + ($techDifference ** 2));
			}

			$resultMessage = [
				'date' => now()->toAtomString(),
				'rows' => [],
			];

			$resultMessage['rows'][] = $this->spyTarget($TargetPlanet, 0, 'fleet_engine.sys_spy_maretials');

			$PlanetFleetInfo = $this->spyTarget($TargetPlanet, 1, 'fleet_engine.sys_spy_fleet');

			if ($ST >= 2) {
				$resultMessage['rows'][] = $PlanetFleetInfo;
			}

			if ($ST >= 3) {
				$resultMessage['rows'][] = $this->spyTarget($TargetPlanet, 2, 'fleet_engine.sys_spy_defenses');
			}

			if ($ST >= 5) {
				$resultMessage['rows'][] = $this->spyTarget($TargetPlanet, 3, 'main.tech.0');
			}

			if ($ST >= 7) {
				$resultMessage['rows'][] = $this->spyTarget($targetUser, 4, 'main.tech.100');
			}

			if ($ST >= 9) {
				$resultMessage['rows'][] = $this->spyTarget($targetUser, 6, 'main.tech.600');
			}

			$totalFleetUnits = array_reduce($PlanetFleetInfo['items'], fn($value, $item) => $value + $item['lv'], 0);

			$TargetForce = ($totalFleetUnits * $spySondeEntity->count) / 4;
			$TargetForce = min(100, max(0, $TargetForce));

			$TargetChances = random_int(0, $TargetForce);
			$SpyerChances = random_int(0, 100);

			if ($TargetChances <= $SpyerChances) {
				$resultMessage['chance'] = $TargetChances;
			} else {
				$resultMessage['chance'] = null;
			}

			$this->fleet->user->notify(
				new SystemMessage(MessageType::Spy, new MissionEspionageMessage($resultMessage))
			);

			$message = new MissionEspionageNotifyMessage([
				'origin_name' => $this->fleet->user_name,
				'origin' => $this->fleet->getOriginCoordinates()->getLink(),
				'target_name' => $TargetPlanet->name,
				'target' => (string) $TargetPlanet->coordinates,
				'chance' => $TargetChances,
			]);

			$TargetPlanet->user->notify(
				new SystemMessage(MessageType::Spy, $message)
			);

			if ($TargetChances > $SpyerChances) {
				$mission = new Attack($this->fleet);
				$mission->targetEvent();
			} else {
				$this->return();
			}
		} else {
			$this->return();
		}
	}

	private function spyTarget(User|Planet $target, int $mode, string $title): array
	{
		if ($mode == 0 && $target instanceof Planet) {
			$result = [
				'type' => 'SpyMessageResourceRow',
				'title' => $title,
				'planet' => [
					'name' => $target->name,
					'galaxy' => $target->galaxy,
					'system' => $target->system,
					'planet' => $target->planet,
					'type' => $target->planet_type,
				],
				'resources' => [
					'metal' => $target->metal,
					'crystal' => $target->crystal,
					'deuterium' => $target->deuterium,
					'energy' => $target->energy,
				],
			];

			if ($targetUser = $target->user) {
				$result['user'] = [
					'id' => $targetUser->id,
					'name' => $targetUser->username,
				];
			}

			return $result;
		}

		$types = [];

		if ($mode == 1) {
			$types[] = ItemType::FLEET;
		} elseif ($mode == 2) {
			$types[] = ItemType::DEFENSE;
		} elseif ($mode == 3) {
			$types[] = ItemType::BUILDING;
		} elseif ($mode == 4) {
			$types[] = ItemType::TECH;
		}

		$result = [
			'type' => 'SpyMessageUnitsRow',
			'title' => $title,
			'items' => [],
		];

		foreach ($types as $type) {
			$items = Vars::getItemsByType($type);

			foreach ($items as $item) {
				$level = 0;

				if ($type == ItemType::BUILDING) {
					$level = $target->getLevel($item);
				} elseif ($type == ItemType::FLEET || $type == ItemType::DEFENSE) {
					$level = $target->getLevel($item);
				} elseif ($type == ItemType::TECH) {
					$level = $target->getTechLevel($item);
				}

				if ($level) {
					$result['items'][] = [
						'id' => $item,
						'lv' => $level,
					];
				}
			}
		}

		if ($mode == 6) {
			$items = Vars::getOfficiers();

			foreach ($items as $item) {
				if ($target->{'officier_' . $item}?->isFuture()) {
					$result['items'][] = [
						'id' => $item,
						'lv' => '+',
					];
				}
			}
		}

		return $result;
	}
}
