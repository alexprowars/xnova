<?php

namespace App\Engine\Battle\Result;

use App\Engine\Battle\BattleResult;
use App\Engine\Battle\Entities\PlayerGroup;
use App\Facades\Vars;
use App\Helpers;

class Result
{
	/** @var Round[] */
	private array $rounds = [];

	public function __construct(private PlayerGroup $attackers, private PlayerGroup $defenders, array $data)
	{
		$this->fillData($data);
	}

	private function fillData(array $data): void
	{
		$this->rounds[] = new Round($this->attackers, $this->defenders, 0, []);

		foreach ($data['rounds'] as $i => $roundData) {
			$this->rounds[] = new Round($this->attackers, $this->defenders, $i + 1, $roundData);
		}
	}

	public function getRound(int $number): ?Round
	{
		return $this->rounds[$number] ?? null;
	}

	public function getFirstRound(): Round
	{
		return array_first($this->rounds);
	}

	public function getLastRound(): Round
	{
		return array_last($this->rounds);
	}

	public function getAttackersLostUnits(bool $repair = true): array
	{
		$before = $this->getFirstRound()->getBattleAttackers();
		$after = $this->getLastRound()->getBattleAttackers();

		return $this->getPlayersLostUnits($before, $after, $repair);
	}

	public function getDefendersLostUnits(bool $repair = true): array
	{
		$before = $this->getFirstRound()->getBattleDefenders();
		$after = $this->getLastRound()->getBattleDefenders();

		return $this->getPlayersLostUnits($before, $after, $repair);
	}

	public function getTotalAttackersLostUnits(): int
	{
		return Helpers::recursiveSum($this->getAttackersLostUnits());
	}

	public function getTotalDefendersLostUnits(): int
	{
		return Helpers::recursiveSum($this->getDefendersLostUnits());
	}

	public function getAttackersResultUnits(): PlayerGroup
	{
		return $this->getPlayersResultUnits(
			$this->getFirstRound()->getBattleAttackers(),
			$this->getLastRound()->getBattleAttackers()
		);
	}

	public function getDefendersResultUnits(): PlayerGroup
	{
		return $this->getPlayersResultUnits(
			$this->getFirstRound()->getBattleDefenders(),
			$this->getLastRound()->getBattleDefenders()
		);
	}

	public function getPlayersResultUnits(PlayerGroup $playersBefore, PlayerGroup $playersAfter): PlayerGroup
	{
		$defRepaired = $this->getPlayerRepaired($playersBefore, $playersAfter);

		$result = clone $playersAfter;

		foreach ($result->getPlayers() as $player) {
			foreach ($player->getFleets() as $fleet) {
				foreach ($fleet->getUnits() as $ship) {
					$repairedAmount = 0;

					if ($repairShip = $defRepaired->getPlayer($player->getId())?->getFleet($fleet->getId())?->getUnit($ship->getId())) {
						$repairedAmount = $repairShip->getCount();
					}

					$ship->increment($repairedAmount);
				}
			}
		}

		return $result;
	}

	private function getPlayersLostUnits(PlayerGroup $playersBefore, PlayerGroup $playersAfter, bool $repair = true): array
	{
		$lostShips = $this->getPlayersLostShips($playersBefore, $playersAfter);
		$defRepaired = $this->getPlayerRepaired($playersBefore, $playersAfter);

		$return = [];

		foreach ($lostShips->getPlayers() as $player) {
			foreach ($player->getFleets() as $fleet) {
				foreach ($fleet->getUnits() as $ship) {
					$repairedAmount = 0;

					if ($repair && ($repairShip = $defRepaired->getPlayer($player->getId())?->getFleet($fleet->getId())?->getUnit($ship->getId()))) {
						$repairedAmount = $repairShip->getCount();
					}

					$costs = Vars::getItemPrice($ship->getId());
					$count = $ship->getCount() - $repairedAmount;

					if ($count > 0) {
						$return[$player->getId()][$fleet->getId()][Vars::getItemType($ship->getId())->value][$ship->getId()] = [
							$costs['metal'] * $count,
							$costs['crystal'] * $count,
						];
					}
				}
			}
		}

		return $return;
	}

	private function getPlayersLostShips(PlayerGroup $playersBefore, PlayerGroup $playersAfter): PlayerGroup
	{
		$result = clone $playersBefore;

		foreach ($playersAfter->getPlayers() as $playerAfter) {
			foreach ($playerAfter->getFleets() as $fleet) {
				foreach ($fleet->getUnits() as $ship) {
					$result->getPlayer($playerAfter->getId())
						?->getFleet($fleet->getId())
						?->getUnit($ship->getId())
						?->increment($ship->getCount() * -1);
				}
			}
		}

		return $result;
	}

	private function getPlayerRepaired(PlayerGroup $playersBefore, PlayerGroup $playersAfter): PlayerGroup
	{
		$lostShips = $this->getPlayersLostShips($playersBefore, $playersAfter);

		foreach ($lostShips->getPlayers() as $player) {
			foreach ($player->getFleets() as $fleet) {
				foreach ($fleet->getUnits() as $ship) {
					$ship->increment(
						((int) ceil($ship->getCount() * (1 - $ship->getRepairProb()))) * -1
					);
				}
			}
		}

		return $lostShips;
	}

	public function getDefendersRepaired(): PlayerGroup
	{
		$defendersBefore = $this->getFirstRound()->getBattleDefenders();
		$defendersAfter = $this->getLastRound()->getBattleDefenders();

		return $this->getPlayerRepaired($defendersBefore, $defendersAfter);
	}

	public function getDebris(): array
	{
		$aDebris = $this->getAttackerDebris();
		$dDebris = $this->getDefenderDebris();

		return ['metal' => $aDebris[0] + $dDebris[0], 'crystal' => $aDebris[1] + $dDebris[1]];
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

					$result[0] += (int) floor($metal * $factor);
					$result[1] += (int) floor($crystal * $factor);
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

					$result[0] += (int) floor($metal * $factor);
					$result[1] += (int) floor($crystal * $factor);
				}
			}
		}

		return $result;
	}

	public function attackerHasWin(): bool
	{
		return $this->getLastRound()->getBattleAttackers()->battleResult === BattleResult::WIN;
	}

	public function defenderHasWin(): bool
	{
		return $this->getLastRound()->getBattleDefenders()->battleResult === BattleResult::WIN;
	}

	public function isAdraw(): bool
	{
		return $this->getLastRound()->getBattleAttackers()->battleResult === BattleResult::DRAW;
	}

	public function getMoonProbability(int $addChance = 0): int
	{
		return min(floor(array_sum($this->getDebris()) / config('battle.MOON_UNIT_PROB')), (config('battle.MAX_MOON_PROB') + $addChance));
	}

	public function getBattleResult(): BattleResult
	{
		return match (true) {
			$this->attackerHasWin() => BattleResult::WIN,
			$this->defenderHasWin() => BattleResult::LOSE,
			default => BattleResult::DRAW,
		};
	}

	public function toArray(): array
	{
		$result = [
			'date' => now()->toAtomString(),
			'attackers' => $this->attackers->convertToBattleResult(),
			'defenders' => $this->defenders->convertToBattleResult(),
			'rounds' => array_map(fn(Round $round) => $round->toArray(), $this->rounds),
			'debris' => $this->getDebris(),
			'lost' => [
				'attackers' => $this->getTotalAttackersLostUnits(),
				'defenders' => $this->getTotalDefendersLostUnits(),
			],
			'moon' => null,
			'moon_probability' => $this->getMoonProbability(),
			'repair' => [],
		];

		$result['won'] = match (true) {
			$this->attackerHasWin() => 1,
			$this->defenderHasWin() => 2,
			default => 0
		};

		foreach ($this->getDefendersRepaired()->getPlayers() as $player) {
			foreach ($player->getFleets() as $fleet) {
				foreach ($fleet->getUnits() as $ship) {
					if ($ship->getCount() > 0) {
						$result['repair'][$fleet->getId()][$ship->getId()] = $ship->getCount();
					}
				}
			}
		}

		return $result;
	}
}
