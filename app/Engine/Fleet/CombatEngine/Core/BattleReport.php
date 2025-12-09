<?php

namespace App\Engine\Fleet\CombatEngine\Core;

use App\Engine\Fleet\CombatEngine\Exception;
use App\Engine\Fleet\CombatEngine\Models\PlayerGroup;
use App\Engine\Fleet\CombatEngine\Utils\Math;

class BattleReport
{
	private $rounds;
	private $roundsCount;
	private $steal;

	public $css = '../../';

	public function __construct()
	{
		$this->rounds = [];
		$this->roundsCount = 0;
		$this->steal = 0;
	}

	public function addRound(Round $round)
	{
		if (config('battle.ONLY_FIRST_AND_LAST_ROUND') && $this->roundsCount == 2) {
			$this->rounds[1] = $round;
			return;
		}
		$this->rounds[$this->roundsCount++] = $round;
	}

	public function getRound($number): Round
	{
		if ($number === 'END') {
			return $this->rounds[$this->roundsCount - 1];
		} elseif ($number === 'START') {
			return $this->rounds[0];
		} elseif ((int) $number < 0 || (int) $number > $this->getLastRoundNumber()) {
			throw new Exception('Invalid round number');
		} else {
			return $this->rounds[(int) $number];
		}
	}

	private function getResultRound($number)
	{
		return $this->getRound($number);
	}

	private function getPresentationRound($number)
	{
		if ($number !== 'START' && $number !== 'END') {
			$number -= 1;
		}
		return $this->getRound($number);
	}

	public function setBattleResult($att, $def)
	{
		$this->getRound('END')->getAfterBattleAttackers()->battleResult = $att;
		$this->getRound('END')->getAfterBattleDefenders()->battleResult = $def;
	}

	public function attackerHasWin()
	{
		return $this->getRound('END')->getAfterBattleAttackers()->battleResult === BattleResult::WIN;
	}

	public function defenderHasWin()
	{
		return $this->getRound('END')->getAfterBattleDefenders()->battleResult === BattleResult::WIN;
	}

	public function isAdraw()
	{
		return $this->getRound('END')->getAfterBattleAttackers()->battleResult === BattleResult::DRAW;
	}

	public function getPresentationAttackersFleetOnRound($number)
	{
		return $this->getPresentationRound($number)->getAfterBattleAttackers();
	}
	public function getPresentationDefendersFleetOnRound($number)
	{
		return $this->getPresentationRound($number)->getAfterBattleDefenders();
	}

	public function getResultAttackersFleetOnRound($number)
	{
		return $this->getResultRound($number)->getAfterBattleAttackers();
	}

	public function getResultDefendersFleetOnRound($number)
	{
		return $this->getResultRound($number)->getAfterBattleDefenders();
	}

	public function getTotalAttackersLostUnits()
	{
		return Math::recursive_sum($this->getAttackersLostUnits());
	}

	public function getTotalDefendersLostUnits()
	{
		return Math::recursive_sum($this->getDefendersLostUnits());
	}

	public function getAttackersLostUnits($repair = true)
	{
		$attackersBefore = $this->getRound('START')->getAfterBattleAttackers();
		$attackersAfter = $this->getRound('END')->getAfterBattleAttackers();

		return $this->getPlayersLostUnits($attackersBefore, $attackersAfter, $repair);
	}

	public function getDefendersLostUnits($repair = true)
	{
		$defendersBefore = $this->getRound('START')->getAfterBattleDefenders();
		$defendersAfter = $this->getRound('END')->getAfterBattleDefenders();

		return $this->getPlayersLostUnits($defendersBefore, $defendersAfter, $repair);
	}

	private function getPlayersLostUnits(PlayerGroup $playersBefore, PlayerGroup $playersAfter, $repair = true)
	{
		$lostShips = $this->getPlayersLostShips($playersBefore, $playersAfter);
		$defRepaired = $this->getPlayerRepaired($playersBefore, $playersAfter);

		$return = [];

		foreach ($lostShips->getIterator() as $idPlayer => $player) {
			foreach ($player->getIterator() as $idFleet => $fleet) {
				foreach ($fleet->getIterator() as $idShipType => $shipType) {
					$cost = $shipType->getCost();
					$repairedAmount = 0;

					if ($repair && $defRepaired->existPlayer($idPlayer) && $defRepaired->getPlayer($idPlayer)->existFleet($idFleet) && $defRepaired->getPlayer($idPlayer)->getFleet($idFleet)->existShipType($idShipType)) {
						$repairedAmount = $defRepaired->getPlayer($idPlayer)->getFleet($idFleet)->getShipType($idShipType)->getCount();
					}

					$count = $shipType->getCount() - $repairedAmount;

					if ($count > 0) {
						$return[$idPlayer][$idFleet][$shipType->getType()][$idShipType] = [$cost[0] * $count, $cost[1] * $count];
					} elseif ($count < 0) {
						throw new Exception('Count negative');
					}
				}
			}
		}
		return $return;
	}

	public function tryMoon()
	{
		$prob = $this->getMoonProb();

		return Math::tryEvent($prob, 'Events::event_moon', $prob);
	}

	public function getMoonProb($add = 0)
	{
		return min(floor(array_sum($this->getDebris()) / config('battle.MOON_UNIT_PROB')), (config('battle.MAX_MOON_PROB') + $add));
	}

	public function getAttackerDebris()
	{
		$result = [0, 0];

		foreach ($this->getAttackersLostUnits(!config('battle.REPAIRED_DO_DEBRIS')) as $idPlayer => $player) {
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

	public function getDefenderDebris()
	{
		$result = [0, 0];

		foreach ($this->getDefendersLostUnits(!config('battle.REPAIRED_DO_DEBRIS')) as $idPlayer => $player) {
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

	public function getDebris()
	{
		$aDebris = $this->getAttackerDebris();
		$dDebris = $this->getDefenderDebris();

		return [$aDebris[0] + $dDebris[0], $aDebris[1] + $dDebris[1]];
	}

	public function getAttackersTech()
	{
		$techs = [];

		$players = $this->getRound('START')->getAfterBattleAttackers()->getIterator()[0];

		foreach ($players->getIterator() as $id => $player) {
			$techs[$player->getId()] = [
				$player->getWeaponsTech(),
				$player->getShieldsTech(),
				$player->getArmourTech()
			];
		}

		return $techs;
	}

	public function getDefendersTech()
	{
		$techs = [];

		$players = $this->getRound('START')->getAfterBattleDefenders()->getIterator()[0];

		foreach ($players->getIterator() as $id => $player) {
			$techs[$player->getId()] = [
				$player->getWeaponsTech(),
				$player->getShieldsTech(),
				$player->getArmourTech()
			];
		}

		return $techs;
	}

	public function getLastRoundNumber()
	{
		return $this->roundsCount - 1;
	}

	public function getDefendersRepaired()
	{
		$defendersBefore = $this->getRound('START')->getAfterBattleDefenders();
		$defendersAfter = $this->getRound('END')->getAfterBattleDefenders();

		return $this->getPlayerRepaired($defendersBefore, $defendersAfter);
	}

	public function getAttackersRepaired()
	{
		$attackersBefore = $this->getRound('START')->getAfterBattleAttackers();
		$attackersAfter = $this->getRound('END')->getAfterBattleAttackers();

		return $this->getPlayerRepaired($attackersBefore, $attackersAfter);
	}

	public function getAfterBattleAttackers()
	{
		$players = $this->getResultAttackersFleetOnRound('END')->cloneMe();
		$playersRepaired = $this->getAttackersRepaired();

		return $this->getAfterBattlePlayerGroup($players, $playersRepaired);
	}

	public function getAfterBattleDefenders()
	{
		$players = $this->getResultDefendersFleetOnRound('END')->cloneMe();
		$playersRepaired = $this->getDefendersRepaired();

		return $this->getAfterBattlePlayerGroup($players, $playersRepaired);
	}

	private function getAfterBattlePlayerGroup(PlayerGroup $players, PlayerGroup $playersRepaired): PlayerGroup
	{
		foreach ($playersRepaired->getIterator() as $idPlayer => $playerRepaired) {
			if (!$players->existPlayer($idPlayer)) { // player is completely destroyed
				$players->addPlayer($playerRepaired);
				continue;
			}

			$endPlayer = $players->getPlayer($idPlayer);

			foreach ($playerRepaired->getIterator() as $idFleet => $fleetRepaired) {
				if (!$endPlayer->existFleet($idFleet)) {
					$endPlayer->addFleet($fleetRepaired);
					continue;
				}

				$endFleet = $endPlayer->getFleet($idFleet);

				foreach ($fleetRepaired->getIterator() as $idShipType => $shipTypeRepaired) {
					$endFleet->addShipType($shipTypeRepaired);
				}
			}
		}

		return $players;
	}

	private function getPlayerRepaired($playersBefore, $playersAfter)
	{
		$lostShips = $this->getPlayersLostShips($playersBefore, $playersAfter);

		foreach ($lostShips->getIterator() as $idPlayer => $player) {
			foreach ($player->getIterator() as $idFleet => $fleet) {
				foreach ($fleet->getIterator() as $idShipType => $shipType) {
					$lostShips->decrement($idPlayer, $idFleet, $idShipType, ceil($shipType->getCount() * (1 - $shipType->getRepairProb())));
				}
			}
		}
		return $lostShips;
	}

	private function getPlayersLostShips(PlayerGroup $playersBefore, PlayerGroup $playersAfter)
	{
		$playersBefore_clone = $playersBefore->cloneMe();

		foreach ($playersAfter->getIterator() as $idPlayer => $playerAfter) {
			foreach ($playerAfter->getIterator() as $idFleet => $fleet) {
				foreach ($fleet->getIterator() as $idShipType => $shipType) {
					$playersBefore_clone->decrement($idPlayer, $idFleet, $idShipType, $shipType->getCount());
				}
			}
		}

		return $playersBefore_clone;
	}

	public function getAttackersId()
	{
		$array = [];

		foreach ($this->getPresentationAttackersFleetOnRound('START') as $id => $group) {
			$array[] = $id;
		}

		return $array;
	}

	public function getDefendersId()
	{
		$array = [];

		foreach ($this->getPresentationDefendersFleetOnRound('START') as $id => $group) {
			$array[] = $id;
		}

		return $array;
	}

	public function setSteal($array)
	{
		$this->steal = $array;
	}

	public function getSteal()
	{
		return $this->steal;
	}
}
