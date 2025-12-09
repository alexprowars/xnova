<?php

namespace App\Engine\Entity\Model;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class TechnologiesEntity implements Arrayable, JsonSerializable
{
	public int $id;
	public int $level = 0;

	public function __construct(array $data)
	{
		$this->id = $data['i'];
		$this->level = $data['l'];
	}

	public static function create(int $id, int $level = 0): self
	{
		return new self(['i' => $id, 'l' => $level]);
	}

	public function toArray(): array
	{
		return [
			'i' => $this->id,
			'l' => $this->level,
		];
	}

	public function jsonSerialize(): array
	{
		return $this->toArray();
	}
}
