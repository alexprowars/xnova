<?php

namespace App\Engine\Battle\Entities;

class Player
{
	private int $id;
	private ?string $name;
	private array $techs = [];
	/** @var Fleet[] */
	protected array $items = [];

	public function __construct(int $id, array $fleets = [], ?string $name = null)
	{
		$this->id = $id;
		$this->name = $name;

		foreach ($fleets as $fleet) {
			$this->addFleet($fleet);
		}
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): self
	{
		$this->name = $name;

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

	public function addFleet(Fleet $fleet): void
	{
		$this->items[$fleet->getId()] = $fleet;
	}

	/**
	 * @return Fleet[]
	 */
	public function getFleets(): array
	{
		return $this->items;
	}

	public function getFleet(int $id): ?Fleet
	{
		return $this->items[$id] ?? null;
	}

	public function existFleet(int $id): bool
	{
		return isset($this->items[$id]);
	}

	public function isEmpty(): bool
	{
		foreach ($this->items as $fleet) {
			if (!$fleet->isEmpty()) {
				return false;
			}
		}

		return true;
	}

	public function __clone()
	{
		foreach ($this->items as $fleet) {
			$this->items[$fleet->getId()] = clone $fleet;
		}
	}
}
