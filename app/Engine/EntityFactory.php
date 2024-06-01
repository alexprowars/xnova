<?php

namespace App\Engine;

use Illuminate\Support\Facades\Auth;
use App\Models\PlanetEntity;
use App\Models\Planet;
use App\Exceptions\Exception;
use App\Vars;

class EntityFactory
{
	public static function get(int $entityId, int $level = 1, ?Planet $planet = null): Entity\Entity
	{
		$className = self::getEntityClassName($entityId);

		if (!$planet) {
			$planet = Auth::user()->getCurrentPlanet(true);
		}

		/** @var Entity\Entity $className */
		return $className::createEntity($entityId, $level, $planet);
	}

	public static function fromModel(PlanetEntity $entity, ?Planet $planet = null)
	{
		$className = self::getEntityClassName($entity->entity_id);

		if (!$planet) {
			$planet = Auth::user()->getCurrentPlanet(true);
		}

		/** @var Entity\Entity $className */
		return $className::createEntity($entity->entity_id, $entity->amount, $planet);
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
