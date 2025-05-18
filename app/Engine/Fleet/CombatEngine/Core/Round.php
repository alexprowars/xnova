<?php

namespace App\Engine\Fleet\CombatEngine\Core;

use App\Engine\Fleet\CombatEngine\CombatObject\Fire;
use App\Engine\Fleet\CombatEngine\CombatObject\FireManager;
use App\Engine\Fleet\CombatEngine\CombatObject\PhysicShot;
use App\Engine\Fleet\CombatEngine\Models\PlayerGroup;

class Round
{
	private $attackers; // PlayerGroup attackers , will be updated when round start
	private $defenders; // PlayerGroup defenders, will be updated when round start

	private $fire_a; // a fire manager that rappresent all fires from attackers to defenders
	private $fire_d; // a fire manager that rappresent all fires from defenders to attackers

	private $physicShotsToDefenders;
	private $physicShotsToAttachers;

	private $attacherShipsCleaner;
	private $defenderShipsCleaner;

	private $number; // this round number

	public function __construct(PlayerGroup $attackers, PlayerGroup $defenders, $number)
	{
		$this->number = $number;
		$this->fire_a = new FireManager();
		$this->fire_d = new FireManager();

		$this->attackers = $attackers->cloneMe();
		$this->defenders = $defenders->cloneMe();
	}

	public function startRound()
	{
		echo '--- Round ' . $this->number . ' ---<br><br>';
		//---------------------- Generating the fire -------------------------------//
		//note that we don't need to check the order of fire, because we will order when splitting the fire later

		// here we add to fire manager each fire shotted from an attacker's ShipType to all defenders
		$defendersMerged = $this->defenders->getEquivalentFleetContent();

		foreach ($this->attackers->getIterator() as $idPlayer => $player) {
			foreach ($player->getIterator() as $idFleet => $fleet) {
				foreach ($fleet->getIterator() as $idShipType => $shipType) {
					$this->fire_a->add(new Fire($shipType, $defendersMerged));
				}
			}
		}
		// here we add to fire manager each fire shotted from an defender's ShipType to all attackers
		$attackersMerged = $this->attackers->getEquivalentFleetContent();
		foreach ($this->defenders->getIterator() as $idPlayer => $player) {
			foreach ($player->getIterator() as $idFleet => $fleet) {
				foreach ($fleet->getIterator() as $idShipType => $shipType) {
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

	public function getAttackersFire()
	{
		return $this->fire_a;
	}

	public function getDefendersFire()
	{
		return $this->fire_d;
	}

	public function getAttachersPhysicShots()
	{
		return $this->physicShotsToDefenders;
	}

	public function getDefendersPhysicShots()
	{
		return $this->physicShotsToAttachers;
	}

	public function getAttachersShipsCleaner()
	{
		return $this->attacherShipsCleaner;
	}

	public function getDefendersShipsCleaner()
	{
		return $this->defenderShipsCleaner;
	}

	public function getAfterBattleAttackers()
	{
		return $this->attackers;
	}

	public function getAfterBattleDefenders()
	{
		return $this->defenders;
	}

	public function getNumber()
	{
		return $this->number;
	}

	public function getAttackersFirePower()
	{
		return $this->getAttackersFire()->getAttackerTotalFire();
	}

	public function getAttackersFireCount()
	{
		return $this->getAttackersFire()->getAttackerTotalShots();
	}

	public function getDefendersFirePower()
	{
		return $this->getDefendersFire()->getAttackerTotalFire();
	}

	public function getDefendersFireCount()
	{
		return $this->getDefendersFire()->getAttackerTotalShots();
	}

	public function getAttachersAssorbedDamage()
	{
		$playerGroupPS = $this->getDefendersPhysicShots();

		return $this->getPlayersAssorbedDamage($playerGroupPS);
	}

	public function getDefendersAssorbedDamage()
	{
		$playerGroupPS = $this->getAttachersPhysicShots();

		return $this->getPlayersAssorbedDamage($playerGroupPS);
	}

	private function getPlayersAssorbedDamage($playerGroupPS)
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
