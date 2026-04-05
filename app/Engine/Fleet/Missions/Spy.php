<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Coordinates;
use App\Engine\Enums\FleetDirection;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\MessageType;
use App\Engine\QueueManager;
use App\Facades\Vars;
use App\Models\Fleet;
use App\Models\Planet;
use App\Models\User;
use App\Notifications\MessageNotification;

class Spy extends BaseMission
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

		if ($owner->rpg_technocrate->isFuture()) {
			$CurrentSpyLvl += 2;
		}

		$TargetSpyLvl = $targetUser->getTechLevel('spy');

		if ($targetUser->rpg_technocrate->isFuture()) {
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
				'type' => 'SpyMessage',
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

			$this->fleet->user->notify(new MessageNotification(null, MessageType::Spy, __('fleet_engine.sys_mess_spy_report'), $resultMessage));

			$TargetMessage  = __('fleet_engine.sys_mess_spy_ennemyfleet') . ' ' . $this->fleet->user_name . ' ';
			$TargetMessage .= $this->fleet->getOriginCoordinates()->getLink();
			$TargetMessage .= __('fleet_engine.sys_mess_spy_seen_at') . ' ' . $TargetPlanet->name;
			$TargetMessage .= ' [' . $TargetPlanet->galaxy . ':' . $TargetPlanet->system . ':' . $TargetPlanet->planet . ']. ';
			$TargetMessage .= sprintf(__('fleet_engine.sys_mess_spy_lostproba'), $TargetChances) . '.';

			$TargetPlanet->user->notify(new MessageNotification(null, MessageType::Spy, __('fleet_engine.sys_mess_spy_activity'), $TargetMessage));

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

	private function spyTarget(User|Planet $TargetPlanet, $Mode, $TitleString)
	{
		if ($Mode == 0) {
			$result = [
				'type' => 'SpyMessageResourceRow',
				'title' => $TitleString,
				'planet' => [
					'name' => $TargetPlanet->name,
					'galaxy' => $TargetPlanet->galaxy,
					'system' => $TargetPlanet->system,
					'planet' => $TargetPlanet->planet,
					'type' => $TargetPlanet->planet_type,
				],
				'resources' => [
					'metal' => $TargetPlanet->metal,
					'crystal' => $TargetPlanet->crystal,
					'deuterium' => $TargetPlanet->deuterium,
					'energy' => $TargetPlanet->energy,
				],
			];

			if ($targetUser = $TargetPlanet->user) {
				$result['user'] = [
					'id' => $targetUser->id,
					'name' => $targetUser->username,
				];
			}

			return $result;
		}

		$types = [];

		if ($Mode == 1) {
			$types[] = ItemType::FLEET;
		} elseif ($Mode == 2) {
			$types[] = ItemType::DEFENSE;
		} elseif ($Mode == 3) {
			$types[] = ItemType::BUILDING;
		} elseif ($Mode == 4) {
			$types[] = ItemType::TECH;
		} elseif ($Mode == 6) {
			$types[] = ItemType::OFFICIER;
		}

		$result = [
			'type' => 'SpyMessageUnitsRow',
			'title' => $TitleString,
			'items' => [],
		];

		foreach ($types as $type) {
			$items = Vars::getItemsByType($type);

			foreach ($items as $item) {
				$level = 0;

				if ($type == ItemType::BUILDING) {
					$level = $TargetPlanet->getLevel($item);
				} elseif ($type == ItemType::FLEET || $type == ItemType::DEFENSE) {
					$level = $TargetPlanet->getLevel($item);
				} elseif ($type == ItemType::OFFICIER) {
					$level = $TargetPlanet->{Vars::getName($item)}->timestamp ?? 0;
				} elseif ($type == ItemType::TECH) {
					$level = $TargetPlanet->getTechLevel($item);
				}

				if (($level && $item < 600) || ($level > time() && $item > 600)) {
					$result['items'][] = [
						'id' => $item,
						'lv' => $item < 600 ? $level : '+',
					];
				}
			}
		}

		return $result;
	}
}
