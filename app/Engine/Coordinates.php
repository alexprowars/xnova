<?php

namespace App\Engine;

use App\Engine\Enums\PlanetType;
use Illuminate\Contracts\Support\Arrayable;

class Coordinates implements Arrayable
{
	public function __construct(protected int $galaxy, protected int $system, protected ?int $planet = null, protected ?PlanetType $type = null)
	{
	}

	public function getGalaxy(): int
	{
		return $this->galaxy;
	}

	public function setGalaxy(int $value): self
	{
		$this->galaxy = $value;

		return $this;
	}

	public function getSystem(): int
	{
		return $this->system;
	}

	public function setSystem(int $value): self
	{
		$this->system = $value;

		return $this;
	}

	public function getPlanet(): ?int
	{
		return $this->planet;
	}

	public function setPlanet(int $value): self
	{
		$this->planet = $value;

		return $this;
	}

	public function getType(): ?PlanetType
	{
		return $this->type;
	}

	public function setType(?PlanetType $value): self
	{
		$this->type = $value;

		return $this;
	}

	public function isEmpty(): bool
	{
		return $this->galaxy <= 0 || $this->system <= 0 || empty($this->planet) || $this->planet <= 0;
	}

	public function isSame(Coordinates $coordinates): bool
	{
		return $this->galaxy == $coordinates->getGalaxy() && $this->system == $coordinates->getSystem() && $this->planet == $coordinates->getPlanet() && $this->type == $coordinates->getType();
	}

	public function toArray(): array
	{
		return [
			'galaxy' => $this->galaxy,
			'system' => $this->system,
			'planet' => $this->planet,
			'planet_type' => $this->type?->value,
		];
	}
}
