<?php

namespace App\Engine\Battle;

use App\Engine\Battle\Engine\BattleCombat;
use App\Engine\Battle\Engine\BattleResult;
use App\Engine\Battle\Engine\Models\Defense;
use App\Engine\Battle\Engine\Models\Fleet;
use App\Engine\Battle\Engine\Models\HomeFleet;
use App\Engine\Battle\Engine\Models\Player;
use App\Engine\Battle\Engine\Models\PlayerGroup;
use App\Engine\Battle\Engine\Models\Ship;
use App\Engine\Battle\Engine\Round;
use App\Engine\Enums\ItemType;
use App\Facades\Vars;
use App\Models\Fleet as FleetModel;
use App\Models\Planet;
use App\Models\User;

class Battle
{
	protected int $rounds = 6;
	protected PlayerGroup $attackers;
	protected PlayerGroup $defenders;

	public function __construct()
	{
		$this->attackers = new PlayerGroup();
		$this->defenders = new PlayerGroup();
	}

	public function addAttackerFleet(FleetModel $fleet)
	{
		$this->addFleetToGroup($this->attackers, $fleet);
	}

	public function addDefenderFleet(FleetModel $fleet)
	{
		$this->addFleetToGroup($this->defenders, $fleet);
	}

	public function addPlanet(Planet $planet)
	{
		$res = [];

		$units = Vars::getItemsByType([ItemType::FLEET, ItemType::DEFENSE]);

		foreach ($units as $i) {
			if ($planet->getLevel($i) > 0) {
				$res[$i] = $planet->getLevel($i);
			}
		}

		foreach (Vars::getItemsByType(ItemType::TECH) as $techId) {
			$level = $planet->user->getTechLevel($techId);

			if ($planet->user->rpg_komandir?->isFuture() && in_array(Vars::getName($techId), ['military_tech', 'defence_tech', 'shield_tech'])) {
				$level += 2;
			}

			if ($level > 0) {
				$res[$techId] = $level;
			}
		}

		$fleet = new HomeFleet(0)
			->setPosition($planet->coordinates);

		foreach ($units as $i) {
			if ($planet->getLevel($i) > 0) {
				$shipType = $this->getShipType($i, $planet->getLevel($i), $res);

				if ($planet->user->rpg_ingenieur && $shipType->getType() == 'Ship') {
					$shipType->setRepairProb(0.8);
				}

				$fleet->addShip($shipType);
			}
		}

		$playerObj = $this->defenders->getPlayer($planet->user->id);

		if (!$playerObj) {
			$playerObj = new Player($planet->user->id, [$fleet])
				->setName($planet->user->username)
				->setTechnologies($res);

			$this->defenders->addPlayer($playerObj);
		}

		$playerObj->addDefense($fleet);
	}

	public function setRounds(int $rounds): self
	{
		$this->rounds = $rounds;

		return $this;
	}

	public function addFleetToGroup(PlayerGroup $group, FleetModel $fleet)
	{
		if ($fleet->entities->isEmpty()) {
			return;
		}

		$res = [];

		foreach ($fleet->entities as $entity) {
			if (Vars::getItemType($entity->id) != ItemType::FLEET) {
				continue;
			}

			$res[$entity->id] = $entity->count;
		}

		$playerObj = $group->getPlayer($fleet->user->id);

		if (!$playerObj) {
			$playerObj = new Player($fleet->user->id)
				->setName($fleet->user->username);
		}

		$playerObj->setTechnologies(
			$res + $this->getUserTechs($fleet->user)
		);

		$fleetObj = new Fleet($fleet->id)
			->setPosition($fleet->getDestinationCoordinates(false));

		foreach ($fleet->entities as $entity) {
			if (Vars::getItemType($entity->id) != ItemType::FLEET || empty($entity->count)) {
				continue;
			}

			$fleetObj->addShip(
				$this->getShipType(
					$entity->id,
					$entity->count,
					$playerObj->getTechnologies()
				)
			);
		}

		if (!$fleetObj->isEmpty()) {
			$playerObj->addFleet($fleetObj);
		}

		if (!$group->existPlayer($playerObj->getId())) {
			$group->addPlayer($playerObj);
		}
	}

	protected function getUserTechs(User $user): array
	{
		$info = [
			'military_tech' => $user->getTechLevel('military'),
			'defence_tech' 	=> $user->getTechLevel('defence'),
			'shield_tech' 	=> $user->getTechLevel('shield'),
			'laser_tech' 	=> $user->getTechLevel('laser'),
			'ionic_tech' 	=> $user->getTechLevel('ionic'),
			'buster_tech' 	=> $user->getTechLevel('buster'),
		];

		if ($user->rpg_komandir?->isFuture()) {
			$info['military_tech'] 	+= 2;
			$info['defence_tech'] 	+= 2;
			$info['shield_tech'] 	+= 2;
		}

		$result = [];

		foreach (Vars::getItemsByType(ItemType::TECH) as $techId) {
			if (isset($info[Vars::getName($techId)])) {
				$result[$techId] = $info[Vars::getName($techId)];
			}
		}

		return $result;
	}

	public function getShipType($id, $count, $res)
	{
		$shipData = Vars::getUnitData($id);

		$attDef 	= ($res[111] ?? 0) * 0.05;
		$attTech 	= ($res[109] ?? 0) * 0.05;

		if ($shipData['type_gun'] == 1) {
			$attTech += ($res[120] ?? 0) * 0.05;
		} elseif ($shipData['type_gun'] == 2) {
			$attTech += ($res[121] ?? 0) * 0.05;
		} elseif ($shipData['type_gun'] == 3) {
			$attTech += ($res[122] ?? 0) * 0.05;
		}

		$price = Vars::getItemPrice($id);

		$cost = [$price['metal'], $price['crystal']];

		if (Vars::getItemType($id) == ItemType::FLEET) {
			return new Ship($id, $count, $shipData['sd'], $shipData['shield'], $cost, $shipData['attack'], $attTech, (($res[110] ?? 0) * 0.05), $attDef);
		}

		return new Defense($id, $count, $shipData['sd'], $shipData['shield'], $cost, $shipData['attack'], $attTech, (($res[110] ?? 0) * 0.05), $attDef);
	}

	protected function checkWhoWon(PlayerGroup $attackers, PlayerGroup $defenders): void
	{
		$attLose = $attackers->isEmpty();
		$defLose = $defenders->isEmpty();

		if ($attLose && !$defLose) {
			$attackers->battleResult = BattleResult::LOSE;
			$defenders->battleResult = BattleResult::WIN;
		} elseif (!$attLose && $defLose) {
			$attackers->battleResult = BattleResult::WIN;
			$defenders->battleResult = BattleResult::LOSE;
		} else {
			$attackers->battleResult = BattleResult::DRAW;
			$defenders->battleResult = BattleResult::DRAW;
		}
	}

	public function run(bool $debug = false): BattleCombat
	{
		if (!$debug) {
			ob_start();
		}

		log_var('attackers', print_r($this->attackers, true));
		log_var('defenders', print_r($this->defenders, true));

		$attackers = clone $this->attackers;
		$defenders = clone $this->defenders;

		$report = new BattleCombat();
		$report->addRound(
			new Round($attackers, $defenders, 0)
		);

		for ($i = 1; $i <= $this->rounds; $i++) {
			$attLose = $attackers->isEmpty();
			$defLose = $defenders->isEmpty();

			if ($attLose || $defLose) {
				$this->checkWhoWon($attackers, $defenders);

				$report->setBattleResult(
					$attackers->battleResult,
					$defenders->battleResult
				);

				if (!$debug) {
					ob_get_clean();
				}

				return $report;
			}

			$round = new Round($attackers, $defenders, $i);
			$round->startRound();

			$report->addRound($round);

			$attackers = $round->getAfterBattleAttackers();
			$defenders = $round->getAfterBattleDefenders();
		}

		$this->checkWhoWon($attackers, $defenders);

		if (!$debug) {
			ob_get_clean();
		}

		return $report;
	}
}
