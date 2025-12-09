<?php

namespace App\Engine\Entity\Model;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class PlanetEntity implements Arrayable, JsonSerializable
{
	public int $id;
	public int $level = 0;
	public int $factor = 10;

	public function __construct(array $data)
	{
		$this->id = $data['i'];
		$this->level = $data['l'];
		$this->factor = $data['f'];
	}

	public static function create(int $id, int $level = 0, int $factor = 10): self
	{
		return new self(['i' => $id, 'l' => $level, 'f' => $factor]);
	}

	public function toArray(): array
	{
		return [
			'i' => $this->id,
			'l' => $this->level,
			'f' => $this->factor,
		];
	}

	public function jsonSerialize(): array
	{
		return $this->toArray();
	}
}
