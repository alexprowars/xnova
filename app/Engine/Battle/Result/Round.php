<?php

namespace App\Engine\Battle\Result;

use App\Engine\Battle\BattleResult;
use App\Engine\Battle\Entities\PlayerGroup;

class Round
{
	private PlayerGroup $attackers;
	private PlayerGroup $defenders;

	private int $hitsAttacker;
	private int $hitsDefender;
	private int $absorbedDamageAttacker;
	private int $absorbedDamageDefender;
	private int $fullStrengthAttacker;
	private int $fullStrengthDefender;

	public function __construct(PlayerGroup $attackers, PlayerGroup $defenders, private int $number, array $data)
	{
		$this->attackers = clone $attackers;
		$this->defenders = clone $defenders;

		$this->fillData($data);
	}

	private function fillData(array $data): void
	{
		if ($this->number > 0) {
			foreach ($this->attackers->getPlayers() as $player) {
				foreach ($player->getFleets() as $fleet) {
					$resultData = $data['attacker_fleet_results'][$fleet->getId()]['units_result'] ?? [];

					foreach ($fleet->getUnits() as $unit) {
						$unit->setCount($resultData[$unit->getId()]['amount'] ?? 0);
					}
				}
			}

			foreach ($this->defenders->getPlayers() as $player) {
				foreach ($player->getFleets() as $fleet) {
					$resultData = $data['defender_fleet_results'][$fleet->getId()]['units_result'] ?? [];

					foreach ($fleet->getUnits() as $unit) {
						$unit->setCount($resultData[$unit->getId()]['amount'] ?? 0);
					}
				}
			}
		}

		$this->hitsAttacker = (int) ($data['hits_attacker'] ?? 0);
		$this->hitsDefender = (int) ($data['hits_defender'] ?? 0);
		$this->absorbedDamageAttacker = (int) ($data['absorbed_damage_attacker'] ?? 0);
		$this->absorbedDamageDefender = (int) ($data['absorbed_damage_defender'] ?? 0);
		$this->fullStrengthAttacker = (int) ($data['full_strength_attacker'] ?? 0);
		$this->fullStrengthDefender = (int) ($data['full_strength_defender'] ?? 0);

		$attLose = $this->attackers->isEmpty();
		$defLose = $this->defenders->isEmpty();

		if ($attLose && !$defLose) {
			$this->attackers->battleResult = BattleResult::LOSE;
			$this->defenders->battleResult = BattleResult::WIN;
		} elseif (!$attLose && $defLose) {
			$this->attackers->battleResult = BattleResult::WIN;
			$this->defenders->battleResult = BattleResult::LOSE;
		} else {
			$this->attackers->battleResult = BattleResult::DRAW;
			$this->defenders->battleResult = BattleResult::DRAW;
		}
	}

	public function getBattleAttackers(): PlayerGroup
	{
		return $this->attackers;
	}

	public function getBattleDefenders(): PlayerGroup
	{
		return $this->defenders;
	}

	public function getNumber(): int
	{
		return $this->number;
	}

	public function getAttackersFirePower(): int
	{
		return $this->fullStrengthAttacker;
	}

	public function getAttackersFireCount(): int
	{
		return $this->hitsAttacker;
	}

	public function getDefendersFirePower(): int
	{
		return $this->fullStrengthDefender;
	}

	public function getDefendersFireCount(): int
	{
		return $this->hitsDefender;
	}

	public function getAttackersAssorbedDamage(): int
	{
		return $this->absorbedDamageAttacker;
	}

	public function getDefendersAssorbedDamage(): int
	{
		return $this->absorbedDamageDefender;
	}

	public function toArray(): array
	{
		$result = [
			'number' => $this->getNumber(),
			'attackers' => [],
			'defenders' => [],
			'hits_attacker' => $this->getAttackersFireCount(),
			'hits_defender' => $this->getDefendersFireCount(),
			'absorbed_damage_attacker' => $this->getAttackersAssorbedDamage(),
			'absorbed_damage_defender' => $this->getDefendersAssorbedDamage(),
			'full_strength_attacker' => $this->getAttackersFirePower(),
			'full_strength_defender' => $this->getDefendersFirePower(),
		];

		foreach ($this->attackers->getPlayers() as $player) {
			foreach ($player->getFleets() as $fleet) {
				foreach ($fleet->getUnits() as $ship) {
					$result['attackers'][$fleet->getId()] ??= [];
					$result['attackers'][$fleet->getId()][$ship->getId()] = $ship->getCount();
				}
			}
		}

		foreach ($this->defenders->getPlayers() as $player) {
			foreach ($player->getFleets() as $fleet) {
				foreach ($fleet->getUnits() as $ship) {
					$result['defenders'][$fleet->getId()] ??= [];
					$result['defenders'][$fleet->getId()][$ship->getId()] = $ship->getCount();
				}
			}
		}

		return $result;
	}
}
