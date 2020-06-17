<?php

namespace Xnova\Planet;

use Illuminate\Contracts\Support\Arrayable;

class Resources implements Arrayable
{
	public const METAL = 'metal';
	public const CRYSTAL = 'crystal';
	public const DEUTERIUM = 'deuterium';
	public const ENERGY = 'energy';

	private $resources;

	public function __construct(array $resources = [])
	{
		$this->resources[self::METAL] = $resources[self::METAL] ?? 0;
		$this->resources[self::CRYSTAL] = $resources[self::CRYSTAL] ?? 0;
		$this->resources[self::DEUTERIUM] = $resources[self::DEUTERIUM] ?? 0;
		$this->resources[self::ENERGY] = $resources[self::ENERGY] ?? 0;
	}

	public static function create(float $metal = 0, float $crystal = 0, float $deuterium = 0, float $energy = 0): self
	{
		return new self([
			self::METAL => $metal,
			self::CRYSTAL => $crystal,
			self::DEUTERIUM => $deuterium,
			self::ENERGY => $energy,
		]);
	}

	public function get(string $type): float
	{
		return $this->resources[$type] ?? 0;
	}

	public function set(string $type, float $value)
	{
		$this->resources[$type] = $value;
	}

	public function add(Resources $resources)
	{
		$this->resources[self::METAL] += $resources->get(self::METAL);
		$this->resources[self::CRYSTAL] += $resources->get(self::CRYSTAL);
		$this->resources[self::DEUTERIUM] += $resources->get(self::DEUTERIUM);
		$this->resources[self::ENERGY] += $resources->get(self::ENERGY);

		return $this;
	}

	public function sub(Resources $resources)
	{
		$this->resources[self::METAL] -= $resources->get(self::METAL);
		$this->resources[self::CRYSTAL] -= $resources->get(self::CRYSTAL);
		$this->resources[self::DEUTERIUM] -= $resources->get(self::DEUTERIUM);
		$this->resources[self::ENERGY] -= $resources->get(self::ENERGY);

		return $this;
	}

	public function multiply(float $value)
	{
		$this->resources[self::METAL] *= $value;
		$this->resources[self::CRYSTAL] *= $value;
		$this->resources[self::DEUTERIUM] *= $value;
		$this->resources[self::ENERGY] *= $value;

		return $this;
	}

	public function toArray(): array
	{
		return $this->resources;
	}
}
