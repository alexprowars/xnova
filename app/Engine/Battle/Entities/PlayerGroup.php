<?php

namespace App\Engine\Battle\Entities;

use App\Engine\Battle\BattleResult;

class PlayerGroup
{
	public ?BattleResult $battleResult = null;
	/** @var Player[] */
	protected array $items = [];

	public function __construct(array $items = [])
	{
		foreach ($items as $item) {
			$this->addPlayer($item);
		}
	}

	public function getPlayer(int $id): ?Player
	{
		return $this->items[$id] ?? null;
	}

	public function existPlayer(int $id): bool
	{
		return isset($this->items[$id]);
	}

	public function addPlayer(Player $player): void
	{
		$this->items[$player->getId()] = $player;
	}

	public function addPlayerIfNotExist(Player $player): Player
	{
		if (!$this->existPlayer($player->getId())) {
			$this->addPlayer($player);
		}

		return $this->getPlayer($player->getId());
	}

	/**
	 * @return Player[]
	 */
	public function getPlayers(): array
	{
		return $this->items;
	}

	public function getPlayersId(): array
	{
		$result = [];

		foreach ($this->getPlayers() as $player) {
			$result[] = $player->getId();
		}

		return array_unique($result);
	}

	public function isEmpty(): bool
	{
		foreach ($this->items as $player) {
			if (!$player->isEmpty()) {
				return false;
			}
		}

		return true;
	}

	public function __clone()
	{
		foreach ($this->items as $player) {
			$this->items[$player->getId()] = clone $player;
		}
	}

	public function convertToBattleInput(): array
	{
		$result = [];

		foreach ($this->getPlayers() as $player) {
			foreach ($player->getFleets() as $fleet) {
				$fleetUnits = [];

				foreach ($fleet->getUnits() as $unit) {
					$fleetUnits[$unit->getId()] = [
						'unit_id' => $unit->getId(),
						'amount' => $unit->getCount(),
						'shield_points' => $unit->getShield(),
						'attack_power' => $unit->getPower(),
						'hull_plating' => $unit->getArmour(),
						'rapidfire' => (object) $unit->getRapidFire(),
					];
				}

				$result[] = [
					'fleet_mission_id' => $fleet->getId(),
					'owner_id' => max(0, $player->getId()),
					'units' => (object) $fleetUnits,
				];
			}
		}

		return $result;
	}

	public function convertToBattleResult(): array
	{
		$result = [];

		foreach ($this->getPlayers() as $player) {
			$techs = $player->getTechnologies();

			$row = [
				'id' => $player->getId(),
				'name' => $player->getName() ?: 'unknown',
				'tech' => [
					'military_tech' => $techs[109] ?? 0,
					'shield_tech' 	=> $techs[110] ?? 0,
					'defence_tech' 	=> $techs[111] ?? 0,
					'laser_tech'	=> $techs[120] ?? 0,
					'ionic_tech'	=> $techs[121] ?? 0,
					'buster_tech'	=> $techs[122] ?? 0,
				],
				'fleet' => [],
				'units' => $techs,
			];

			foreach ($player->getFleets() as $fleet) {
				$row['fleet'][] = [
					'id' => $fleet->getId(),
					...$fleet->getPosition()->toArray(),
				];
			}

			$result[$player->getId()] = $row;
		}

		return $result;
	}
}
