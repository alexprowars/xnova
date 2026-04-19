<?php

namespace App\Engine\Objects;

use App\Exceptions\Exception;
use App\Facades\Vars;

class ObjectsFactory
{
	public static function get(mixed $objectId): BaseObject
	{
		$data = Vars::getItemObject($objectId);

		if (!$data) {
			throw new Exception('unknown object');
		}

		return $data;
	}

	public static function make(array $data): BaseObject
	{
		$class = match ($data['type'] ?? null) {
			'building' => BuildingObject::class,
			'research' => ResearchObject::class,
			'fleet' => ShipObject::class,
			'defense' => DefenceObject::class,
			default => throw new Exception('unknown object type')
		};

		return new $class($data);
	}
}
