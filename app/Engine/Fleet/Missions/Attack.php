<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Battle\Battle;
use App\Engine\Coordinates;
use App\Engine\Entity\Model\FleetEntity;
use App\Engine\Entity\Model\FleetEntityCollection;
use App\Engine\Enums\FleetDirection;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\MessageType;
use App\Engine\Enums\PlanetType;
use App\Engine\Fleet\FleetEngine;
use App\Engine\Fleet\MissionType as MissionEnum;
use App\Engine\Messages\Types\MissionAttackMessage;
use App\Engine\QueueManager;
use App\Facades\Galaxy;
use App\Facades\Vars;
use App\Models;
use App\Models\Fleet as FleetModel;
use App\Models\LogsAttack;
use App\Models\Planet;
use App\Models\User;
use App\Notifications\SystemMessage;
use App\Services\FleetService;
use Illuminate\Support\Facades\DB;

class Attack extends BaseMission
{
	public static function isMissionPossible(Planet $planet, Coordinates $target, ?Planet $targetPlanet, array $units = [], bool $isAssault = false): bool
	{
		if (!in_array($target->getType(), [PlanetType::PLANET, PlanetType::MOON, PlanetType::MILITARY_BASE])) {
			return false;
		}

		if (!$targetPlanet) {
			return false;
		}

		if ($planet->user_id == $targetPlanet->user_id) {
			return false;
		}

		if (!empty($units[208]) || !empty($units[209]) || !empty($units[216])) {
			return false;
		}

		return true;
	}

	public function targetEvent(): void
	{
		$target = Planet::findByCoordinates($this->fleet->getDestinationCoordinates());

		if (!$target || !$target->user_id || $target->destroyed_at) {
			$this->return();

			return;
		}

		if (!$this->fleet->user) {
			$this->return();

			return;
		}

		$targetUser = $target->user;

		if (!$targetUser) {
			$this->return();

			return;
		}

		$target->getProduction($this->fleet->start_date)->update();

		$queueManager = new QueueManager($target);
		$queueManager->checkUnitQueue();

		$battle = new Battle();

		if ($this->checkFleet($this->fleet)) {
			$battle->addAttackerFleet($this->fleet);
		} else {
			return;
		}

		if ($this->fleet->assault_id) {
			$fleets = Models\Fleet::where('id', $this->fleet->id)
				->where('assault_id', $this->fleet->assault_id)
				->get()
				->filter(fn(Models\Fleet $fleet) => $this->checkFleet($fleet));

			foreach ($fleets as $fleet) {
				$battle->addAttackerFleet($fleet);
			}
		}

		$fleets = Models\Fleet::query()
			->coordinates(FleetDirection::END, $this->fleet->getDestinationCoordinates())
			->where('mess', 3)
			->get()
			->filter(fn(Models\Fleet $fleet) => $this->checkFleet($fleet));

		foreach ($fleets as $fleet) {
			$battle->addDefenderFleet($fleet);
		}

		$battle->addPlanet($target);

		$report = $battle->run();
		$result = $report->toArray();

		$attackFleets  = $report->getAttackersResultUnits();
		$defenseFleets = $report->getDefendersResultUnits();

		$steal = [
			'metal' => 0,
			'crystal' => 0,
			'deuterium' => 0,
		];

		if ($result['won'] == 1) {
			$maxStorage = 0;
			$maxFleetStorage = [];

			foreach ($attackFleets->getPlayers() as $player) {
				foreach ($player->getFleets() as $fleet) {
					$units = FleetEntityCollection::createFromArray($fleet->getUnitsCount())
						->filter(fn(FleetEntity $entity) => $entity->id !== 210);

					$maxFleetStorage[$fleet->getId()] = $units->getCapacity();

					$maxStorage += $maxFleetStorage[$fleet->getId()];
				}
			}

			$res_correction = $maxStorage;
			$res_procent = [];

			if ($maxStorage > 0) {
				foreach ($maxFleetStorage as $id => $res) {
					$res_procent[$id] = $res / $res_correction;
				}
			}

			$steal = FleetService::getSteal($target, $maxStorage);
		}

		$totalDebris = array_sum($result['debris']);

		if ($totalDebris > 0) {
			Planet::query()->coordinates(new Coordinates($target->galaxy, $target->system, $target->planet))
				->whereNot('planet_type', PlanetType::MOON)
				->incrementEach([
					'debris_metal' => $result['debris']['metal'],
					'debris_crystal' => $result['debris']['crystal'],
				]);
		}

		foreach ($attackFleets->getPlayers() as $player) {
			foreach ($player->getFleets() as $fleet) {
				$units = FleetEntityCollection::createFromArray($fleet->getUnitsCount());

				if ($units->isEmpty()) {
					$this->killFleet($fleet->getId());
				} else {
					$update = [
						'entities' 		=> $units,
						'updated_at' 	=> DB::raw('end_date'),
						'mess'			=> 1,
						'assault_id'	=> null,
						'won'			=> $result['won'],
					];

					if ($result['won'] == 1 && isset($res_procent[$fleet->getId()]) && ($steal['metal'] > 0 || $steal['crystal'] > 0 || $steal['deuterium'] > 0)) {
						$update['resource_metal'] 		= DB::raw('resource_metal + ' . (int) round($res_procent[$fleet->getId()] * $steal['metal']));
						$update['resource_crystal'] 	= DB::raw('resource_crystal + ' . (int) round($res_procent[$fleet->getId()] * $steal['crystal']));
						$update['resource_deuterium'] 	= DB::raw('resource_deuterium + ' . (int) round($res_procent[$fleet->getId()] * $steal['deuterium']));
					}

					Models\Fleet::query()->whereKey($fleet->getId())->update($update);
				}
			}
		}

		foreach ($defenseFleets->getPlayers() as $player) {
			foreach ($player->getFleets() as $fleet) {
				if ($fleet->getId() != 0) {
					$units = FleetEntityCollection::createFromArray($fleet->getUnitsCount());

					if ($units->isEmpty()) {
						$this->killFleet($fleet->getId());
					} else {
						Models\Fleet::query()->whereKey($fleet->getId())
							->update([
								'entities' => $units,
								'updated_at' => DB::raw('end_date'),
							]);
					}
				} else {
					if ($steal['metal'] > 0 || $steal['crystal'] > 0 || $steal['deuterium'] > 0) {
						$target->metal -= $steal['metal'];
						$target->crystal -= $steal['crystal'];
						$target->deuterium -= $steal['deuterium'];
					}

					$units = Vars::getItemsByType([
						ItemType::FLEET,
						ItemType::DEFENSE
					]);

					foreach ($units as $i) {
						if (($unit = $fleet->getUnit($i)) && $target->getLevel($i) > 0) {
							$target->updateAmount($i, $unit->getCount());
						}
					}

					$target->update();
				}
			}
		}

		$moonProbability = $report->getMoonProbability($targetUser->officier_admiral?->isFuture() ? 10 : 0);

		if ($target->planet_type != PlanetType::PLANET) {
			$moonProbability = 0;
		}

		$userMoonProbability = random_int(1, 100);

		if ($this->fleet->end_type == PlanetType::MILITARY_BASE) {
			$userMoonProbability = 0;
		}

		if (!$target->moon_id && $userMoonProbability && $userMoonProbability <= $moonProbability) {
			$moon = Galaxy::createMoon(
				$this->fleet->getDestinationCoordinates(),
				$target->user,
				$moonProbability
			);

			if ($moon) {
				$result['moon'] = 1;
			} else {
				$result['moon'] = 2;
			}
		} else {
			$result['moon'] = 0;
		}

		// Очки военного опыта
		$warPoints 		= round($totalDebris / 25000);
		$AddWarPoints 	= ($result['won'] != 2) ? $warPoints : 0;
		// Сборка массива ID участников боя
		$FleetsUsers = [];

		$tmp = [];

		foreach ($report->getFirstRound()->getBattleAttackers()->getPlayersId() as $userId) {
			if (!in_array($userId, $tmp)) {
				$tmp[] = $userId;
			}
		}

		$realAttackersUsers = count($tmp);
		unset($tmp);

		foreach ($report->getFirstRound()->getBattleAttackers()->getPlayersId() as $userId) {
			$FleetsUsers[] = $userId;

			if ($this->fleet->mission == MissionEnum::Spy) {
				continue;
			}

			$update = ['raids' => DB::raw('raids + 1')];

			if ($result['won'] == 1) {
				$update['raids_win'] = DB::raw('raids_win + 1');
			} elseif ($result['won'] == 2) {
				$update['raids_lose'] = DB::raw('raids_lose + 1');
			}

			if ($AddWarPoints > 0) {
				$update['xpraid'] = DB::raw('xpraid + ' . ceil($AddWarPoints / $realAttackersUsers));
			}

			Models\User::query()->whereKey($userId)->update($update);
		}

		foreach ($report->getFirstRound()->getBattleDefenders()->getPlayersId() as $userId) {
			$FleetsUsers[] = $userId;

			if ($this->fleet->mission == MissionEnum::Spy) {
				continue;
			}

			$update = ['raids' => DB::raw('raids + 1')];

			if ($result['won'] == 2) {
				$update['raids_win'] = DB::raw('raids_win + 1');
			} elseif ($result['won'] == 1) {
				$update['raids_lose'] = DB::raw('raids_lose + 1');
			}

			Models\User::query()->whereKey($userId)->update($update);
		}

		// Уничтожен в первой волне
		$noContact = (count($result['rounds']) <= 2 && $result['won'] == 2) ? 1 : 0;

		$combatReport = Models\Report::create([
			'users_id' 		=> array_unique($FleetsUsers),
			'no_contact' 	=> $noContact,
			'data' 			=> $result,
		]);

		if ($this->fleet->assault) {
			$this->fleet->assault->delete();
		}

		FleetService::checkHallBattle($combatReport);

		$reportData = [
			'report_id' => $combatReport->id,
			'galaxy' => $this->fleet->end_galaxy,
			'system' => $this->fleet->end_system,
			'planet' => $this->fleet->end_planet,
			'lost' => $result['lost'],
			'steal' => $steal,
			'debris' => $result['debris'],
		];

		$reportData['color'] = match ($result['won']) {
			1 => 'green',
			2 => 'red',
			default => 'orange'
		};

		foreach ($report->getFirstRound()->getBattleAttackers()->getPlayersId() as $userId) {
			User::findOne($userId)?->notify(
				new SystemMessage(MessageType::Battle, new MissionAttackMessage($reportData))
			);
		}

		unset(
			$reportData['steal'],
			$reportData['debris'],
			$reportData['lost']
		);

		$reportData['color'] = match ($result['won']) {
			1 => 'red',
			2 => 'green',
			default => 'orange'
		};

		foreach ($report->getFirstRound()->getBattleDefenders()->getPlayersId() as $userId) {
			User::findOne($userId)?->notify(
				new SystemMessage(MessageType::Battle, new MissionAttackMessage($reportData))
			);
		}

		LogsAttack::create([
			'user_id' 		=> $this->fleet->user_id,
			'planet_start' 	=> 0,
			'planet_end'	=> $target->id,
			'fleet' 		=> $this->fleet->entities,
			'battle_log'	=> $combatReport->id,
		]);
	}

	public function checkFleet(FleetModel $fleet): bool
	{
		if (($fleet->entities->isEmpty() && $fleet->mission == MissionEnum::Attack) || ($fleet->mission == MissionEnum::Assault && $fleet->entities->count() == 1 && $fleet->entities->getByEntityId(210))) {
			(new FleetEngine($fleet))->return();

			return false;
		}

		return true;
	}
}
