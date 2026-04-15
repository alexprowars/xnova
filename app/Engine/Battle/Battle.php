<?php

namespace App\Engine\Battle;

use App\Engine\Battle\Entities\Fleet;
use App\Engine\Battle\Entities\Player;
use App\Engine\Battle\Entities\PlayerGroup;
use App\Engine\Battle\Entities\Unit;
use App\Engine\Battle\Result\Result;
use App\Engine\Enums\ItemType;
use App\Facades\Vars;
use App\Models\Fleet as FleetModel;
use App\Models\Planet;
use App\Models\User;
use FFI;

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

	public function addAttackerFleet(FleetModel $fleet): void
	{
		$this->addFleetToGroup($this->attackers, $fleet);
	}

	public function addDefenderFleet(FleetModel $fleet): void
	{
		$this->addFleetToGroup($this->defenders, $fleet);
	}

	public function addPlanet(Planet $planet): void
	{
		$this->addPlanetToGroup($this->defenders, $planet);
	}

	public function addFleetToGroup(PlayerGroup $group, FleetModel $fleet): void
	{
		if ($fleet->entities->isEmpty()) {
			return;
		}

		$res = [];

		foreach ($fleet->entities as $entity) {
			if (Vars::getItemType($entity->id) != ItemType::FLEET && Vars::getItemType($entity->id) != ItemType::DEFENSE) {
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
			if ((Vars::getItemType($entity->id) != ItemType::FLEET && Vars::getItemType($entity->id) != ItemType::DEFENSE) || empty($entity->count)) {
				continue;
			}

			$fleetObj->addUnit(
				$this->getUnitData(
					$entity->id,
					$entity->count,
					$playerObj->getTechnologies()
				)
			);
		}

		if (!$fleetObj->isEmpty()) {
			$playerObj->addFleet($fleetObj);
		}

		$group->addPlayerIfNotExist($playerObj);
	}

	public function addPlanetToGroup(PlayerGroup $group, Planet $planet): void
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

		$fleet = new Fleet(0)
			->setPosition($planet->coordinates);

		foreach ($units as $i) {
			if ($planet->getLevel($i) > 0) {
				$unit = $this->getUnitData($i, $planet->getLevel($i), $res);

				if ($planet->user->rpg_ingenieur && Vars::getItemType($unit->getId()) == ItemType::FLEET) {
					$unit->setRepairProb(0.8);
				}

				$fleet->addUnit($unit);
			}
		}

		$playerObj = $group->getPlayer($planet->user->id);

		if (!$playerObj) {
			$playerObj = new Player($planet->user->id, [$fleet])
				->setName($planet->user->username)
				->setTechnologies($res);

			$group->addPlayer($playerObj);
		}

		$playerObj->addFleet($fleet);
	}

	public function run()
	{
		$ffi = FFI::cdef(
			"char* fight_battle_rounds(const char* input_json);",
			base_path('storage/libbattle_engine_ffi.so')
		);

		$inputJson = json_encode([
			'attacker_fleets' => $this->attackers->convertToBattleInput(),
			'defender_fleets' => $this->defenders->convertToBattleInput(),
		]);

		/** @noinspection PhpUndefinedMethodInspection, @phpstan-ignore-next-line */
		$outputPtr = $ffi->fight_battle_rounds($inputJson);
		$output = FFI::string($outputPtr);
		$output = json_decode($output, true);

		return new Result($this->attackers, $this->defenders, $output);
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

	protected function getUnitData(int|string $id, int $count, array $res = []): Unit
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

		return new Unit(
			$id,
			$count,
			(int) round($shipData['attack'] * (1 + $attTech)),
			(int) round((array_sum($cost) * (1 + $attDef)) / 10),
			(int) round($shipData['shield'] * (1 + (($res[110] ?? 0) * 0.05))),
			$shipData['sd'] ?? []
		);
	}
}
