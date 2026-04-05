<?php

namespace App\Engine\Battle\Engine\Models;

use App\Engine\Battle\Engine\Objects\FireManager;

class Player
{
	private int $id;
	private string $name;
	private array $techs = [];
	/** @var Fleet[] */
	protected array $array = [];

	public function __construct(int $id, array $fleets = [], string $name = '')
	{
		$this->id = $id;
		$this->name = $name;

		foreach ($fleets as $fleet) {
			$this->addFleet($fleet);
		}
	}
	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): self
	{
		$this->name = $name;

		foreach ($this->array as $fleet) {
			$fleet->setName($name);
		}

		return $this;
	}

	public function getTechnologies(): array
	{
		return $this->techs;
	}

	public function setTechnologies(array $items): self
	{
		$this->techs = $items;

		return $this;
	}

	public function addFleet(Fleet $fleet)
	{
		$fleet = $fleet->cloneMe();
		$fleet->setName($this->name);
		$this->array[$fleet->getId()] = $fleet;
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function decrement($idFleet, $idShipType, $count): self
	{
		$this->array[$idFleet]->decrement($idShipType, $count);

		if ($this->array[$idFleet]->isEmpty()) {
			unset($this->array[$idFleet]);
		}

		return $this;
	}

	public function getOrderedItereator()
	{
		$this->order();
		return $this->array;
	}

	private function order(): void
	{
		ksort($this->array);
	}

	/**
	 * @return Fleet[]
	 */
	public function getFleets(): array
	{
		return $this->array;
	}

	public function getFleet($id): ?Fleet
	{
		return $this->array[$id] ?? null;
	}

	public function existFleet($idFleet): bool
	{
		return isset($this->array[$idFleet]);
	}

	public function isEmpty(): bool
	{
		foreach ($this->array as $fleet) {
			if (!$fleet->isEmpty()) {
				return false;
			}
		}

		return true;
	}

	public function inflictDamage(FireManager $fire): array
	{
		$physicShots = [];

		foreach ($this->array as $idFleet => $fleet) {
			echo "------- firing to fleet with ID = $idFleet -------- <br>";
			$ps = $fleet->inflictDamage($fire);
			$physicShots[$idFleet] = $ps;
		}

		return $physicShots;
	}

	public function cleanShips(): array
	{
		$shipsCleaners = [];

		foreach ($this->array as $idFleet => $fleet) {
			echo "------- cleanShips to fleet with ID = $idFleet -------- <br>";
			$sc = $fleet->cleanShips();
			$shipsCleaners[$this->getId()] = $sc;

			if ($fleet->isEmpty()) {
				unset($this->array[$idFleet]);
			}
		}

		return $shipsCleaners;
	}

	public function repairShields($round = 0): void
	{
		foreach ($this->array as $fleet) {
			$fleet->repairShields($round);
		}
	}

	public function getEquivalentFleetContent(): Fleet
	{
		$merged = new Fleet(-1);

		foreach ($this->array as $fleet) {
			$merged->mergeFleet($fleet);
		}

		return $merged;
	}

	public function addDefense(Fleet $fleetDefender): void
	{
		$fleetDefender = $fleetDefender->cloneMe();
		$this->order();
		$fl = current($this->array);

		if ($fl === false) {
			$this->array[$fleetDefender->getId()] = $fleetDefender;//avoid collateral effects: when the object or array is an argument && it's saved in a structure
		} else {
			$fl->mergeFleet($fleetDefender);
		}
	}

	public function mergePlayerFleets(self $player): void
	{
		foreach ($player->getFleets() as $fleet) {
			$this->array[$fleet->getId()] = $fleet->cloneMe();
		}
	}

	public function getTotalCount(): int
	{
		$amount = 0;

		foreach ($this->array as $fleet) {
			$amount += $fleet->getTotalCount();
		}

		return $amount;
	}

	public function cloneMe(): self
	{
		$item = new Player($this->id, array_values($this->array), $this->name);
		$item->setTechnologies($this->getTechnologies());

		return $item;
	}
}
