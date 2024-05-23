<?php

namespace App\Planet;

use Illuminate\Support\Facades\Auth;
use App\Entity\Research;
use App\Models\PlanetEntity;
use App\Planet;
use App\Planet\Entity\Building;
use App\Exceptions\Exception;
use App\Planet\Entity\Defence;
use App\Planet\Entity\Ship;
use App\Vars;

class EntityFactory
{
	public static function create(int $entityId, int $level = 1, ?Planet $planet = null): Planet\Entity\BaseEntity
	{
		$className = self::getEntityClassName($entityId);

		if (!$planet) {
			$planet = Auth::user()->getCurrentPlanet(true);
		}

		/** @var Planet\Entity\BaseEntity $className */
		return $className::createEntity($entityId, $level, $planet);
	}

	public static function createFromModel(PlanetEntity $entity, ?Planet $planet = null)
	{
		$className = self::getEntityClassName($entity->entity_id);

		if (!$planet) {
			$planet = Auth::user()->getCurrentPlanet(true);
		}

		/** @var Planet\Entity\BaseEntity $object */
		$object = new $className($entity->getAttributes());
		$object->exists = $entity->exists;
		$object->planet()->associate($planet);
		$object->syncOriginal();

		return $object;
	}

	public static function getEntityClassName(int $entityId): string
	{
		$entityType = Vars::getItemType($entityId);

		switch ($entityType) {
			case Vars::ITEM_TYPE_BUILING:
				return Building::class;
			case Vars::ITEM_TYPE_TECH:
				return Research::class;
			case Vars::ITEM_TYPE_FLEET:
				return Ship::class;
			case Vars::ITEM_TYPE_DEFENSE:
				return Defence::class;
			default:
				throw new Exception('unknown entity');
		}
	}
}
