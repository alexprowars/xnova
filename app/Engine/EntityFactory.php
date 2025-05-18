<?php

namespace App\Engine;

use App\Engine\Enums\ItemType;
use App\Exceptions\Exception;
use App\Facades\Vars;
use App\Models\Planet;
use Illuminate\Support\Facades\Auth;

class EntityFactory
{
	public static function get(int $entityId, int $level = 1, ?Planet $planet = null): Entity\Entity
	{
		$className = self::getEntityClassName($entityId);

		if (!$planet) {
			$planet = Auth::user()->getCurrentPlanet();
		}

		/** @var class-string<Entity\Entity> $className */
		return $className::createEntity($entityId, $level, $planet);
	}

	public static function getEntityClassName(int $entityId): string
	{
		return match (Vars::getItemType($entityId)) {
			ItemType::BUILDING => Entity\Building::class,
			ItemType::TECH => Entity\Research::class,
			ItemType::FLEET => Entity\Ship::class,
			ItemType::DEFENSE => Entity\Defence::class,
			default => throw new Exception('unknown entity'),
		};
	}
}
