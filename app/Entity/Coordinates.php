<?php

namespace App\Entity;

use Illuminate\Contracts\Support\Arrayable;

class Coordinates implements Arrayable
{
	public const TYPE_PLANET = 1;
	public const TYPE_DEBRIS = 2;
	public const TYPE_MOON = 3;
	public const TYPE_MILITARY_BASE = 5;

	public function __construct(private int $galaxy, private int $system, private ?int $planet = null, private ?int $type = null)
	{
	}

	public function getGalaxy(): int
	{
		return $this->galaxy;
	}

	public function getSystem(): int
	{
		return $this->system;
	}

	public function getPlanet(): ?int
	{
		return $this->planet;
	}

	public function getType(): ?int
	{
		return $this->type;
	}

	public function isEmpty(): bool
	{
		return $this->galaxy <= 0 || $this->system <= 0 || $this->planet <= 0;
	}

	public function toArray(): array
	{
		return [
			'galaxy' => $this->galaxy,
			'system' => $this->system,
			'planet' => $this->planet,
			'planet_type' => $this->type,
		];
	}
}
