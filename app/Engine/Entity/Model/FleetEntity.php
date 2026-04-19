<?php

namespace App\Engine\Entity\Model;

use App\Engine\Objects\BaseObject;
use App\Engine\Objects\ObjectsFactory;
use App\Engine\Objects\ShipObject;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * @implements Arrayable<string, mixed>
 */
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

	public function getParam(string $key, mixed $default = null): mixed
	{
		return $this->params[$key] ?? $default;
	}

	/**
	 * @return array{i: int, c: int, p: array}
	 */
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

	/**
	 * @return array{i: int, c: int, p: array}
	 */
	public function jsonSerialize(): array
	{
		return $this->toArray();
	}

	public function getObjectData(): BaseObject
	{
		return ObjectsFactory::get($this->id);
	}

	public function getCapacity(): int
	{
		$object = $this->getObjectData();

		if (!($object instanceof ShipObject)) {
			return 0;
		}

		return $this->count * $object->getCapacity();
	}
}
