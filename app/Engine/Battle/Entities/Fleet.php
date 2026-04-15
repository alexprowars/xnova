<?php

namespace App\Engine\Battle\Entities;

use App\Engine\Coordinates;

class Fleet
{
	private int $id;
	private int $count = 0;
	private ?Coordinates $position;
	/** @var Unit[] */
	protected array $items = [];

	public function __construct(int $id, array $items = [], ?Coordinates $position = null)
	{
		$this->id = $id;
		$this->position = $position;

		foreach ($items as $item) {
			$this->addUnit($item);
		}
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getPosition(): ?Coordinates
	{
		return $this->position;
	}

	public function setPosition(Coordinates $position): self
	{
		$this->position = $position;

		return $this;
	}

	public function addUnit(Unit $unit)
	{
		if (isset($this->items[$unit->getId()])) {
			$this->items[$unit->getId()]->increment($unit->getCount());
		} else {
			$this->items[$unit->getId()] = $unit;
		}

		$this->count += $unit->getCount();
	}

	public function getUnit(int $id): ?Unit
	{
		return $this->items[$id] ?? null;
	}

	public function getUnits(): array
	{
		return $this->items;
	}

	public function existUnit(int $id): bool
	{
		return isset($this->items[$id]);
	}

	public function getUnitCount(int $id): int
	{
		return $this->getUnit($id)?->getCount() ?? 0;
	}

	public function getUnitsCount(): array
	{
		$result = [];

		foreach ($this->getUnits() as $unit) {
			$result[$unit->getId()] = $unit->getCount();
		}

		return $result;
	}

	public function getTotalCount(): int
	{
		return $this->count;
	}

	public function isEmpty(): bool
	{
		foreach ($this->items as $unit) {
			if (!$unit->isEmpty()) {
				return false;
			}
		}

		return true;
	}

	public function __clone()
	{
		foreach ($this->items as $unit) {
			$this->items[$unit->getId()] = clone $unit;
		}
	}
}
