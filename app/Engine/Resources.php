<?php

namespace App\Engine;

use App\Engine\Enums\Resources as ResourcesEnum;
use Illuminate\Contracts\Support\Arrayable;

class Resources implements Arrayable
{
	protected array $resources;

	public function __construct(array $resources = [])
	{
		foreach (ResourcesEnum::cases() as $res) {
			$this->resources[$res->value] = $resources[$res->value] ?? 0;
		}
	}

	public static function create(float $metal = 0, float $crystal = 0, float $deuterium = 0, float $energy = 0): self
	{
		return new self([
			ResourcesEnum::METAL->value => $metal,
			ResourcesEnum::CRYSTAL->value => $crystal,
			ResourcesEnum::DEUTERIUM->value => $deuterium,
			ResourcesEnum::ENERGY->value => $energy,
		]);
	}

	public function get(string|ResourcesEnum $type): float
	{
		if ($type instanceof ResourcesEnum) {
			$type = $type->value;
		}

		return $this->resources[$type] ?? 0;
	}

	public function set(string|ResourcesEnum $type, float $value)
	{
		if ($type instanceof ResourcesEnum) {
			$type = $type->value;
		}

		$this->resources[$type] = $value;
	}

	public function add(Resources $resources)
	{
		foreach (ResourcesEnum::cases() as $res) {
			$this->resources[$res->value] += $resources->get($res);
		}

		return $this;
	}

	public function sub(Resources $resources)
	{
		foreach (ResourcesEnum::cases() as $res) {
			$this->resources[$res->value] -= $resources->get($res);
		}

		return $this;
	}

	public function multiply(float $value, array $except = [])
	{
		foreach (ResourcesEnum::cases() as $res) {
			if (!empty($except) && in_array($res, $except)) {
				continue;
			}

			$this->resources[$res->value] *= $value;
		}

		return $this;
	}

	public function toArray(): array
	{
		return $this->resources;
	}
}
