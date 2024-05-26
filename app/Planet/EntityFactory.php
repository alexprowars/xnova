<?php

namespace App\Planet;

use Illuminate\Support\Facades\Auth;
use App\Entity\Research;
use App\Models\PlanetEntity;
use App\Models\Planet;
use App\Planet\Entity\Building;
use App\Exceptions\Exception;
use App\Planet\Entity\Defence;
use App\Planet\Entity\Ship;
use App\Vars;

class EntityFactory
{
	public static function create(int $entityId, int $level = 1, ?Planet $planet = null): Entity\BaseEntity
	{
		$className = self::getEntityClassName($entityId);

		if (!$planet) {
			$planet = Auth::user()->getCurrentPlanet(true);
		}

		/** @var Entity\BaseEntity $className */
		return $className::createEntity($entityId, $level, $planet);
	}

	public static function createFromModel(PlanetEntity $entity, ?Planet $planet = null)
	{
		$className = self::getEntityClassName($entity->entity_id);

		if (!$planet) {
			$planet = Auth::user()->getCurrentPlanet(true);
		}

		/** @var Entity\BaseEntity $object */
		$object = new $className($entity->getAttributes());
		$object->exists = $entity->exists;
		$object->planet()->associate($planet);
		$object->syncOriginal();

		return $object;
	}

	public static function getEntityClassName(int $entityId): string
	{
		$entityType = Vars::getItemType($entityId);

		return match ($entityType) {
			Vars::ITEM_TYPE_BUILING => Building::class,
			Vars::ITEM_TYPE_TECH => Research::class,
			Vars::ITEM_TYPE_FLEET => Ship::class,
			Vars::ITEM_TYPE_DEFENSE => Defence::class,
			default => throw new Exception('unknown entity'),
		};
	}
}
