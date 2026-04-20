<?php

namespace App\Engine\Battle\Entities;

use App\Engine\Enums\ItemType;
use App\Facades\Vars;

class Unit
{
	private ?float $repairProb = null;

	public function __construct(private int $id, private int $count, private int $power, private int $armour, private int $shield, private array $rapidfire = [])
	{
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getCount(): int
	{
		return $this->count;
	}

	public function setCount(int $count): void
	{
		$this->count = $count;
	}

	public function increment(int $count): void
	{
		$this->count += $count;
	}

	public function getPower(): int
	{
		return $this->power;
	}

	public function getArmour(): int
	{
		return $this->armour;
	}

	public function getShield(): int
	{
		return $this->shield;
	}

	/**
	 * @return array<int, int>
	 */
	public function getRapidFire(): array
	{
		return $this->rapidfire;
	}

	public function isEmpty(): bool
	{
		return $this->count == 0;
	}

	public function setRepairProb(float $factor = 0): void
	{
		$this->repairProb = $factor;
	}

	public function getRepairProb(): float
	{
		if ($this->repairProb !== null) {
			return $this->repairProb;
		}

		if (Vars::getItemType($this->id) == ItemType::DEFENSE) {
			return config('game.combat.defenseRepairProbability', 0);
		}

		return config('game.combat.shipRepairProbability', 0);
	}
}
