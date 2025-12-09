<?php

namespace App\Engine\Entity\Model;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class FleetEntity implements Arrayable, JsonSerializable
{
	public int $id;
	public int $count = 0;
	public array $params = [];

	public function __construct(array $data)
	{
		$this->id = $data['i'];
		$this->count = $data['c'];
		$this->params = $data['p'] ?? [];
	}

	public static function create(int $id, int $count = 0, array $params = []): self
	{
		return new self(['i' => $id, 'c' => $count, 'p' => $params]);
	}

	public function getParam(string $key, $default = null): mixed
	{
		return $this->params[$key] ?? $default;
	}

	public function toArray(): array
	{
		$result = [
			'i' => $this->id,
			'c' => $this->count,
		];

		if (!empty($this->params)) {
			$result['p'] = $this->params;
		}

		return $result;
	}

	public function jsonSerialize(): array
	{
		return $this->toArray();
	}
}
