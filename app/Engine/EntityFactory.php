<?php

namespace App\Engine;

use App\Exceptions\Exception;
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

		/** @var Entity\Entity $className */
		return $className::createEntity($entityId, $level, $planet);
	}

	public static function getEntityClassName(int $entityId): string
	{
		return match (Vars::getItemType($entityId)) {
			Vars::ITEM_TYPE_BUILING => Entity\Building::class,
			Vars::ITEM_TYPE_TECH => Entity\Research::class,
			Vars::ITEM_TYPE_FLEET => Entity\Ship::class,
			Vars::ITEM_TYPE_DEFENSE => Entity\Defence::class,
			default => throw new Exception('unknown entity'),
		};
	}
}
