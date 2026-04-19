<?php

namespace App\Engine;

use App\Engine\Enums\ItemType;
use App\Engine\Objects\BaseObject;
use App\Exceptions\Exception;
use App\Facades\Vars;
use App\Models\Planet;
use Illuminate\Support\Facades\Auth;

class EntityFactory
{
	/**
	 * @param int $entityId
	 * @param int $level
	 * @param Planet|null $planet
	 * @return Entity\Entity<BaseObject>
	 * @throws Exception
	 */
	public static function get(int $entityId, int $level = 1, ?Planet $planet = null): Entity\Entity
	{
		$className = self::getEntityClassName($entityId);

		if (!$planet) {
			$planet = Auth::user()->getCurrentPlanet();
		}

		/** @var class-string<Entity\Entity<BaseObject>> $className */
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
