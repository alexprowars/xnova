<?php

namespace App\Engine\Fleet\CombatEngine;

use App\Engine\Enums\ItemType;
use App\Engine\Fleet\CombatEngine\Core\Battle;
use App\Engine\Fleet\CombatEngine\Core\Round;
use App\Engine\Fleet\CombatEngine\Models\Defense;
use App\Engine\Fleet\CombatEngine\Models\Fleet;
use App\Engine\Fleet\CombatEngine\Models\Player;
use App\Engine\Fleet\CombatEngine\Models\PlayerGroup;
use App\Engine\Fleet\CombatEngine\Models\Ship;
use App\Engine\Fleet\CombatEngine\Models\ShipType;
use App\Facades\Vars;

class Simulation
{
	protected array $slots = [];
	protected array $usersInfo = [];
	protected ?array $result = null;

	public function addSlot(array $items)
	{
		$this->slots[] = $items;
	}

	public function getResult(): array
	{
		if (empty($this->result)) {
			$this->handle();
		}

		return $this->result;
	}

	public function handle()
	{
		$maxSlots = config('game.maxSlotsInSim', 5);

		$attackers = $this->getAttackers(0);
		$defenders = $this->getAttackers($maxSlots);

		$engine = new Battle($attackers, $defenders);

		$report = $engine->getReport();

		$result = [];
		$result[0] = ['time' => time(), 'rw' => []];

		$result[1] = $this->convertPlayerGroupToArray($report->getResultAttackersFleetOnRound('START'));
		$result[2] = $this->convertPlayerGroupToArray($report->getResultDefendersFleetOnRound('START'));

		for ($_i = 0; $_i <= $report->getLastRoundNumber(); $_i++) {
			$result[0]['rw'][] = $this->convertRoundToArray($report->getRound($_i));
		}

		if ($report->attackerHasWin()) {
			$result[0]['won'] = 1;
		}
		if ($report->defenderHasWin()) {
			$result[0]['won'] = 2;
		}
		if ($report->isAdraw()) {
			$result[0]['won'] = 0;
		}

		$result[0]['lost'] = ['att' => $report->getTotalAttackersLostUnits(), 'def' => $report->getTotalDefendersLostUnits()];

		$debris = $report->getDebris();

		$result[0]['debree']['att'] = $debris;
		$result[0]['debree']['def'] = [0, 0];

		$result[3] = ['metal' => 0, 'crystal' => 0, 'deuterium' => 0];
		$result[4] = $report->getMoonProb();
		$result[5] = '';

		$result[6] = [];

		foreach ($report->getDefendersRepaired() as $_player) {
			foreach ($_player as $_idFleet => $_fleet) {
				/** @var ShipType $_ship */
				foreach ($_fleet as $_shipID => $_ship) {
					$result[6][$_idFleet][$_shipID] = $_ship->getCount();
				}
			}
		}

		$this->result = $result;
	}

	public function getStatistics(): array
	{
		$maxSlots = config('game.maxSlotsInSim', 5);

		$attackers = $this->getAttackers(0);
		$defenders = $this->getAttackers($maxSlots);

		$statistics = [];

		for ($i = 0; $i < 50; $i++) {
			$report = new Battle($attackers, $defenders)
				->getReport();

			$statistics[] = ['att' => $report->getTotalAttackersLostUnits(), 'def' => $report->getTotalDefendersLostUnits()];

			unset($report);
		}

		uasort($statistics, fn($a, $b) => ($a['att'] > $b['att'] ? 1 : -1));

		return array_values($statistics);
	}

	private function convertPlayerGroupToArray(PlayerGroup $_playerGroup)
	{
		$result = [];

		foreach ($_playerGroup as $_player) {
			$result[$_player->getId()] = [
				'username' => $_player->getName(),
				'fleet' => [$_player->getId() => ['galaxy' => 1, 'system' => 1, 'planet' => 1]],
				'tech' => [
					'military_tech' => $this->usersInfo[$_player->getId()][109] ?? 0,
					'shield_tech' 	=> $this->usersInfo[$_player->getId()][110] ?? 0,
					'defence_tech' 	=> $this->usersInfo[$_player->getId()][111] ?? 0,
					'laser_tech'	=> $this->usersInfo[$_player->getId()][120] ?? 0,
					'ionic_tech'	=> $this->usersInfo[$_player->getId()][121] ?? 0,
					'buster_tech'	=> $this->usersInfo[$_player->getId()][122] ?? 0,
				],
				'flvl' => $this->usersInfo[$_player->getId()],
			];
		}

		return $result;
	}

	private function convertRoundToArray(Round $round)
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

		foreach ($attackers as $_player) {
			foreach ($_player as $_idFleet => $_fleet) {
				/**
				 * @var ShipType $_ship
				 */
				foreach ($_fleet as $_shipID => $_ship) {
					$result['attackers'][$_idFleet][$_shipID] = $_ship->getCount();

					if (!isset($result['attackA'][$_idFleet]['total'])) {
						$result['attackA'][$_idFleet]['total'] = 0;
					}

					$result['attackA'][$_idFleet]['total'] += $_ship->getCount();
				}
			}
		}

		foreach ($defenders as $_player) {
			foreach ($_player as $_idFleet => $_fleet) {
				/**
				 * @var ShipType $_ship
				 */
				foreach ($_fleet as $_shipID => $_ship) {
					$result['defenders'][$_idFleet][$_shipID] = $_ship->getCount();

					if (!isset($result['defenseA'][$_idFleet]['total'])) {
						$result['defenseA'][$_idFleet]['total'] = 0;
					}

					$result['defenseA'][$_idFleet]['total'] += $_ship->getCount();
				}
			}
		}

		$result['attackShield'] = $round->getAttachersAssorbedDamage();
		$result['defShield'] 	= $round->getDefendersAssorbedDamage();

		return $result;
	}

	private function getAttackers($s)
	{
		$maxSlots = config('game.maxSlotsInSim', 5);

		$playerGroupObj = new PlayerGroup();

		for ($i = $s; $i < $maxSlots * 2; $i++) {
			if ($i <= $maxSlots && $i < ($maxSlots + $s) && !empty($this->slots[$i])) {
				$res = [];
				$fleets = [];

				$rFleet = $this->slots[$i];

				foreach ($rFleet as $shipArr) {
					if ($shipArr['id'] > 200) {
						$fleets[$shipArr['id']] = [$shipArr['count'], 0];
					}

					$res[$shipArr['id']] = $shipArr['count'];
				}

				$fleetId = $i;
				$playerId = $i;

				$playerObj = new Player($playerId);
				$playerObj->setName('Игрок ' . ($playerId + 1));
				$playerObj->setTech(0, 0, 0);

				$this->usersInfo[$playerId] = $res;

				$fleetObj = new Fleet($fleetId);

				foreach ($fleets as $id => $count) {
					$id = floor($id);

					if ($count[0] > 0 && $id > 0) {
						$fleetObj->addShipType($this->getShipType($id, $count, $res));
					}
				}

				if (!$fleetObj->isEmpty()) {
					$playerObj->addFleet($fleetObj);
				}

				if (!$playerGroupObj->existPlayer($playerId)) {
					$playerGroupObj->addPlayer($playerObj);
				}
			}
		}

		return $playerGroupObj;
	}

	private function getShipType($id, $count, $res)
	{
		$attDef 	= $count[1] + ($res[111] ?? 0) * 0.05;
		$attTech 	= ($res[109] ?? 0) * 0.05 + $count[1];

		$unitData = Vars::getUnitData($id);

		if ($unitData['type_gun'] == 1) {
			$attTech += ($res[120] ?? 0) * 0.05;
		} elseif ($unitData['type_gun'] == 2) {
			$attTech += ($res[121] ?? 0) * 0.05;
		} elseif ($unitData['type_gun'] == 3) {
			$attTech += ($res[122] ?? 0) * 0.05;
		}

		$price = Vars::getItemPrice($id);

		$cost = [$price['metal'], $price['crystal']];

		if (Vars::getItemType($id) == ItemType::FLEET) {
			return new Ship($id, $count[0], $unitData['sd'], $unitData['shield'], $cost, $unitData['attack'], $attTech, (($res[110] ?? 0) * 0.05), $attDef);
		}

		return new Defense($id, $count[0], $unitData['sd'], $unitData['shield'], $cost, $unitData['attack'], $attTech, (($res[110] ?? 0) * 0.05), $attDef);
	}
}
