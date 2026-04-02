<?php

namespace App\Engine\Fleet\CombatEngine\Core;

use App\Engine\Fleet\CombatEngine\CombatObject\Fire;
use App\Engine\Fleet\CombatEngine\CombatObject\FireManager;
use App\Engine\Fleet\CombatEngine\CombatObject\PhysicShot;
use App\Engine\Fleet\CombatEngine\Models\PlayerGroup;

class Round
{
	private PlayerGroup $attackers; // PlayerGroup attackers , will be updated when round start
	private PlayerGroup $defenders; // PlayerGroup defenders, will be updated when round start

	private FireManager $fire_a; // a fire manager that rappresent all fires from attackers to defenders
	private FireManager $fire_d; // a fire manager that rappresent all fires from defenders to attackers

	private array $physicShotsToDefenders = [];
	private array $physicShotsToAttachers = [];

	private array $attacherShipsCleaner = [];
	private array $defenderShipsCleaner = [];

	private int $number; // this round number

	public function __construct(PlayerGroup $attackers, PlayerGroup $defenders, int $number)
	{
		$this->number = $number;
		$this->fire_a = new FireManager();
		$this->fire_d = new FireManager();

		$this->attackers = $attackers->cloneMe();
		$this->defenders = $defenders->cloneMe();
	}

	public function startRound(): void
	{
		echo '--- Round ' . $this->number . ' ---<br><br>';
		//---------------------- Generating the fire -------------------------------//
		//note that we don't need to check the order of fire, because we will order when splitting the fire later

		// here we add to fire manager each fire shotted from an attacker's ShipType to all defenders
		$defendersMerged = $this->defenders->getEquivalentFleetContent();

		foreach ($this->attackers->getIterator() as $player) {
			foreach ($player->getIterator() as $fleet) {
				foreach ($fleet->getIterator() as $shipType) {
					$this->fire_a->add(new Fire($shipType, $defendersMerged));
				}
			}
		}
		// here we add to fire manager each fire shotted from an defender's ShipType to all attackers
		$attackersMerged = $this->attackers->getEquivalentFleetContent();
		foreach ($this->defenders->getIterator() as $player) {
			foreach ($player->getIterator() as $fleet) {
				foreach ($fleet->getIterator() as $shipType) {
					$this->fire_d->add(new Fire($shipType, $attackersMerged));
				}
			}
		}
		//--------------------------------------------------------------------------//

		//------------------------- Sending the fire -------------------------------//
		echo "***** firing to defenders *****<br>";
		$this->physicShotsToDefenders = $this->defenders->inflictDamage($this->fire_a);
		echo "***** firing to attackers *****<br>";
		$this->physicShotsToAttachers = $this->attackers->inflictDamage($this->fire_d);
		//--------------------------------------------------------------------------//

		//------------------------- Cleaning ships ---------------------------------//
		$this->defenderShipsCleaner = $this->defenders->cleanShips();
		$this->attacherShipsCleaner = $this->attackers->cleanShips();
		//--------------------------------------------------------------------------//

		//------------------------- Repairing shields ------------------------------//
		$this->defenders->repairShields($this->number);
		$this->attackers->repairShields($this->number);
		//--------------------------------------------------------------------------//
	}

	public function getAttackersFire(): FireManager
	{
		return $this->fire_a;
	}

	public function getDefendersFire(): FireManager
	{
		return $this->fire_d;
	}

	public function getAttachersPhysicShots(): array
	{
		return $this->physicShotsToDefenders;
	}

	public function getDefendersPhysicShots(): array
	{
		return $this->physicShotsToAttachers;
	}

	public function getAttachersShipsCleaner(): array
	{
		return $this->attacherShipsCleaner;
	}

	public function getDefendersShipsCleaner(): array
	{
		return $this->defenderShipsCleaner;
	}

	public function getAfterBattleAttackers(): PlayerGroup
	{
		return $this->attackers;
	}

	public function getAfterBattleDefenders(): PlayerGroup
	{
		return $this->defenders;
	}

	public function getNumber(): int
	{
		return $this->number;
	}

	public function getAttackersFirePower(): int
	{
		return $this->getAttackersFire()->getAttackerTotalFire();
	}

	public function getAttackersFireCount(): int
	{
		return $this->getAttackersFire()->getAttackerTotalShots();
	}

	public function getDefendersFirePower(): int
	{
		return $this->getDefendersFire()->getAttackerTotalFire();
	}

	public function getDefendersFireCount(): int
	{
		return $this->getDefendersFire()->getAttackerTotalShots();
	}

	public function getAttachersAssorbedDamage(): float
	{
		$playerGroupPS = $this->getDefendersPhysicShots();

		return $this->getPlayersAssorbedDamage($playerGroupPS);
	}

	public function getDefendersAssorbedDamage(): float
	{
		$playerGroupPS = $this->getAttachersPhysicShots();

		return $this->getPlayersAssorbedDamage($playerGroupPS);
	}

	private function getPlayersAssorbedDamage($playerGroupPS): float
	{
		$ass = 0;

		if (!is_array($playerGroupPS)) {
			return $ass;
		}

		foreach ($playerGroupPS as $playerPs) {
			foreach ($playerPs as $fleetPS) {
				foreach ($fleetPS as $typeDPS) {
					foreach ($typeDPS as $typeAPS) {
						/** @var PhysicShot $typeAPS */
						$ass += $typeAPS->getAssorbedDamage();
					}
				}
			}
		}

		return $ass;
	}
}
