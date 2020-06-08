<?php

namespace Xnova\Entity;

class Coordinates
{
	public const TYPE_PLANET = 1;
	public const TYPE_DEBRIS = 2;
	public const TYPE_MOON = 3;
	public const TYPE_MILITARY_BASE = 5;

	private $galaxy;
	private $system;
	private $position;
	private $type;

	public function __construct(int $galaxy, int $system, ?int $position = null, ?int $type = null)
	{
		$this->galaxy = $galaxy;
		$this->system = $system;
		$this->position = $position;
		$this->type = $type;
	}

	public function getGalaxy(): int
	{
		return $this->galaxy;
	}

	public function getSystem(): int
	{
		return $this->system;
	}

	public function getPosition(): ?int
	{
		return $this->position;
	}

	public function getType(): ?int
	{
		return $this->type;
	}

	public function isEmpty(): bool
	{
		return $this->galaxy < 0 || $this->system < 0 || $this->position < 0;
	}
}
