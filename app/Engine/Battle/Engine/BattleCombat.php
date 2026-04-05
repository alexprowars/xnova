<?php

namespace App\Engine\Battle\Engine;

use App\Engine\Battle\Engine\Models\PlayerGroup;
use App\Engine\Battle\Engine\Utils\Math;

class BattleCombat
{
	protected array $rounds = [];
	protected int $roundsCount = 0;

	public function addRound(Round $round): void
	{
		if (config('battle.ONLY_FIRST_AND_LAST_ROUND') && $this->roundsCount == 2) {
			$this->rounds[1] = $round;
			return;
		}

		$this->rounds[$this->roundsCount++] = $round;
	}

	public function getRound(mixed $number): Round
	{
		if ($number === 'END') {
			return $this->rounds[$this->roundsCount - 1];
		} elseif ($number === 'START') {
			return $this->rounds[0];
		} elseif ((int) $number < 0 || (int) $number > $this->getLastRoundNumber()) {
			throw new Exception('Invalid round number');
		}

		return $this->rounds[(int) $number];
	}

	private function getResultRound($number): Round
	{
		return $this->getRound($number);
	}

	private function getPresentationRound($number): Round
	{
		if ($number !== 'START' && $number !== 'END') {
			--$number;
		}

		return $this->getRound($number);
	}

	public function setBattleResult(BattleResult $att, BattleResult $def)
	{
		$this->getRound('END')->getAfterBattleAttackers()->battleResult = $att;
		$this->getRound('END')->getAfterBattleDefenders()->battleResult = $def;
	}

	public function attackerHasWin(): bool
	{
		return $this->getRound('END')->getAfterBattleAttackers()->battleResult === BattleResult::WIN;
	}

	public function defenderHasWin(): bool
	{
		return $this->getRound('END')->getAfterBattleDefenders()->battleResult === BattleResult::WIN;
	}

	public function isAdraw(): bool
	{
		return $this->getRound('END')->getAfterBattleAttackers()->battleResult === BattleResult::DRAW;
	}

	public function getPresentationAttackersFleetOnRound($number): PlayerGroup
	{
		return $this->getPresentationRound($number)->getAfterBattleAttackers();
	}

	public function getPresentationDefendersFleetOnRound($number): PlayerGroup
	{
		return $this->getPresentationRound($number)->getAfterBattleDefenders();
	}

	public function getResultAttackersFleetOnRound($number): PlayerGroup
	{
		return $this->getResultRound($number)->getAfterBattleAttackers();
	}

	public function getResultDefendersFleetOnRound($number): PlayerGroup
	{
		return $this->getResultRound($number)->getAfterBattleDefenders();
	}

	public function getTotalAttackersLostUnits(): int
	{
		return Math::recursive_sum($this->getAttackersLostUnits());
	}

	public function getTotalDefendersLostUnits(): int
	{
		return Math::recursive_sum($this->getDefendersLostUnits());
	}

	public function getAttackersLostUnits(bool $repair = true): array
	{
		$attackersBefore = $this->getRound('START')->getAfterBattleAttackers();
		$attackersAfter = $this->getRound('END')->getAfterBattleAttackers();

		return $this->getPlayersLostUnits($attackersBefore, $attackersAfter, $repair);
	}

	public function getDefendersLostUnits(bool $repair = true): array
	{
		$defendersBefore = $this->getRound('START')->getAfterBattleDefenders();
		$defendersAfter = $this->getRound('END')->getAfterBattleDefenders();

		return $this->getPlayersLostUnits($defendersBefore, $defendersAfter, $repair);
	}

	private function getPlayersLostUnits(PlayerGroup $playersBefore, PlayerGroup $playersAfter, bool $repair = true): array
	{
		$lostShips = $this->getPlayersLostShips($playersBefore, $playersAfter);
		$defRepaired = $this->getPlayerRepaired($playersBefore, $playersAfter);

		$return = [];

		foreach ($lostShips->getPlayers() as $player) {
			foreach ($player->getFleets() as $fleet) {
				foreach ($fleet->getShips() as $ship) {
					$cost = $ship->getCost();
					$repairedAmount = 0;

					if ($repair && ($repairShip = $defRepaired->getPlayer($player->getId())?->getFleet($fleet->getId())?->getShip($ship->getId()))) {
						$repairedAmount = $repairShip->getCount();
					}

					$count = $ship->getCount() - $repairedAmount;

					if ($count > 0) {
						$return[$player->getId()][$fleet->getId()][$ship->getType()][$ship->getId()] = [$cost[0] * $count, $cost[1] * $count];
					} elseif ($count < 0) {
						throw new Exception('Count negative');
					}
				}
			}
		}
		return $return;
	}

	public function getMoonProb(int $add = 0): int
	{
		return min(floor(array_sum($this->getDebris()) / config('battle.MOON_UNIT_PROB')), (config('battle.MAX_MOON_PROB') + $add));
	}

	public function getAttackerDebris(): array
	{
		$result = [0, 0];

		foreach ($this->getAttackersLostUnits(!config('battle.REPAIRED_DO_DEBRIS')) as $player) {
			foreach ($player as $fleet) {
				foreach ($fleet as $role => $values) {
					$metal = 0;
					$crystal = 0;

					foreach ($values as $lost) {
						$metal += $lost[0];
						$crystal += $lost[1];
					}

					$factor = config('battle.' . strtoupper($role) . '_DEBRIS_FACTOR');

					$result[0] += $metal * $factor;
					$result[1] += $crystal * $factor;
				}
			}
		}

		return $result;
	}

	public function getDefenderDebris(): array
	{
		$result = [0, 0];

		foreach ($this->getDefendersLostUnits(!config('battle.REPAIRED_DO_DEBRIS')) as $player) {
			foreach ($player as $fleet) {
				foreach ($fleet as $role => $values) {
					$metal = 0;
					$crystal = 0;

					foreach ($values as $lost) {
						$metal += $lost[0];
						$crystal += $lost[1];
					}

					$factor = config('battle.' . strtoupper($role) . '_DEBRIS_FACTOR');

					$result[0] += $metal * $factor;
					$result[1] += $crystal * $factor;
				}
			}
		}

		return $result;
	}

	public function getDebris(): array
	{
		$aDebris = $this->getAttackerDebris();
		$dDebris = $this->getDefenderDebris();

		return [$aDebris[0] + $dDebris[0], $aDebris[1] + $dDebris[1]];
	}

	public function getLastRoundNumber(): int
	{
		return $this->roundsCount - 1;
	}

	public function getDefendersRepaired(): PlayerGroup
	{
		$defendersBefore = $this->getRound('START')->getAfterBattleDefenders();
		$defendersAfter = $this->getRound('END')->getAfterBattleDefenders();

		return $this->getPlayerRepaired($defendersBefore, $defendersAfter);
	}

	public function getAttackersRepaired(): PlayerGroup
	{
		$attackersBefore = $this->getRound('START')->getAfterBattleAttackers();
		$attackersAfter = $this->getRound('END')->getAfterBattleAttackers();

		return $this->getPlayerRepaired($attackersBefore, $attackersAfter);
	}

	public function getAfterBattleAttackers(): PlayerGroup
	{
		$players = $this->getResultAttackersFleetOnRound('END')->cloneMe();
		$playersRepaired = $this->getAttackersRepaired();

		return $this->getAfterBattlePlayerGroup($players, $playersRepaired);
	}

	public function getAfterBattleDefenders(): PlayerGroup
	{
		$players = $this->getResultDefendersFleetOnRound('END')->cloneMe();
		$playersRepaired = $this->getDefendersRepaired();

		return $this->getAfterBattlePlayerGroup($players, $playersRepaired);
	}

	private function getAfterBattlePlayerGroup(PlayerGroup $players, PlayerGroup $playersRepaired): PlayerGroup
	{
		foreach ($playersRepaired->getPlayers() as $playerRepaired) {
			if (!$players->existPlayer($playerRepaired->getId())) {
				$players->addPlayer($playerRepaired);
				continue;
			}

			$endPlayer = $players->getPlayer($playerRepaired->getId());

			foreach ($playerRepaired->getFleets() as $fleetRepaired) {
				if (!$endPlayer->existFleet($fleetRepaired->getId())) {
					$endPlayer->addFleet($fleetRepaired);
					continue;
				}

				$endFleet = $endPlayer->getFleet($fleetRepaired->getId());

				foreach ($fleetRepaired->getShips() as $ship) {
					$endFleet->addShip($ship);
				}
			}
		}

		return $players;
	}

	private function getPlayerRepaired(PlayerGroup $playersBefore, PlayerGroup $playersAfter): PlayerGroup
	{
		$lostShips = $this->getPlayersLostShips($playersBefore, $playersAfter);

		foreach ($lostShips->getPlayers() as $player) {
			foreach ($player->getFleets() as $fleet) {
				foreach ($fleet->getShips() as $ship) {
					$lostShips->decrement($player->getId(), $fleet->getId(), $ship->getId(), ceil($ship->getCount() * (1 - $ship->getRepairProb())));
				}
			}
		}

		return $lostShips;
	}

	private function getPlayersLostShips(PlayerGroup $playersBefore, PlayerGroup $playersAfter): PlayerGroup
	{
		$playersBefore_clone = $playersBefore->cloneMe();

		foreach ($playersAfter->getPlayers() as $playerAfter) {
			foreach ($playerAfter->getFleets() as $fleet) {
				foreach ($fleet->getShips() as $ship) {
					$playersBefore_clone->decrement($playerAfter->getId(), $fleet->getId(), $ship->getId(), $ship->getCount());
				}
			}
		}

		return $playersBefore_clone;
	}

	public function getAttackersId(): array
	{
		$result = [];

		foreach ($this->getPresentationAttackersFleetOnRound('START')->getPlayers() as $player) {
			$result[] = $player->getId();
		}

		return array_unique($result);
	}

	public function getDefendersId(): array
	{
		$result = [];

		foreach ($this->getPresentationDefendersFleetOnRound('START')->getPlayers() as $player) {
			$result[] = $player->getId();
		}

		return array_unique($result);
	}

	public function convertPlayerGroupToArray(PlayerGroup $playerGroup): array
	{
		$result = [];

		foreach ($playerGroup->getPlayers() as $player) {
			$techs = $player->getTechnologies();

			$result[$player->getId()] = [
				'id'	=> $player->getId(),
				'name'	=> $player->getName(),
				'fleet'	=> [],
				'tech'	=> [
					'military_tech' => $techs[109] ?? 0,
					'shield_tech' 	=> $techs[110] ?? 0,
					'defence_tech' 	=> $techs[111] ?? 0,
					'laser_tech'	=> $techs[120] ?? 0,
					'ionic_tech'	=> $techs[121] ?? 0,
					'buster_tech'	=> $techs[122] ?? 0,
				],
				'units' => $techs,
			];

			$fleets = $player->getFleets();

			foreach ($fleets as $fleet) {
				$result[$player->getId()]['fleet'][] = [
					'id' => $fleet->getId(),
					...$fleet->getPosition()->toArray(),
				];
			}
		}

		return $result;
	}

	public function convertRoundToArray(Round $round): array
	{
		$result = [
			'attackers' 	=> [],
			'defenders' 	=> [],
			'attack'		=> ['total' => $round->getAttackersFirePower()],
			'defense' 		=> ['total' => $round->getDefendersFirePower()],
			'attackA' 		=> ['total' => $round->getAttackersFireCount()],
			'defenseA' 		=> ['total' => $round->getDefendersFireCount()],
		];

		$attackers = $round->getAfterBattleAttackers();
		$defenders = $round->getAfterBattleDefenders();

		foreach ($attackers->getPlayers() as $player) {
			foreach ($player->getFleets() as $fleet) {
				foreach ($fleet->getShips() as $ship) {
					$result['attackers'][$fleet->getId()][$ship->getId()] = $ship->getCount();

					$result['attackA'][$fleet->getId()]['total'] ??= 0;
					$result['attackA'][$fleet->getId()]['total'] += $ship->getCount();
				}
			}
		}

		foreach ($defenders->getPlayers() as $player) {
			foreach ($player->getFleets() as $fleet) {
				foreach ($fleet->getShips() as $ship) {
					$result['defenders'][$fleet->getId()][$ship->getId()] = $ship->getCount();

					$result['defenseA'][$fleet->getId()]['total'] ??= 0;
					$result['defenseA'][$fleet->getId()]['total'] += $ship->getCount();
				}
			}
		}

		$result['attackShield'] = $round->getAttachersAssorbedDamage();
		$result['defShield'] 	= $round->getDefendersAssorbedDamage();

		return $result;
	}

	public function convertToArray(): array
	{
		$result = [
			'date' => now()->toISOString(),
			'rw' => [],
			'debris' => [],
		];

		for ($_i = 0; $_i <= $this->getLastRoundNumber(); $_i++) {
			$result['rw'][] = $this->convertRoundToArray($this->getRound($_i));
		}

		$result['won'] = 0;

		if ($this->attackerHasWin()) {
			$result['won'] = 1;
		}

		if ($this->defenderHasWin()) {
			$result['won'] = 2;
		}

		if ($this->isAdraw()) {
			$result['won'] = 0;
		}

		$result['lost'] = [
			'att' => $this->getTotalAttackersLostUnits(),
			'def' => $this->getTotalDefendersLostUnits(),
		];

		$result['debris']['att'] = $this->getDebris();
		$result['debris']['def'] = [0, 0];

		return $result;
	}
}
