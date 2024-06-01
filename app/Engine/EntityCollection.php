<?php

namespace App\Engine;

use App\Engine\Entity\Entity;
use App\Models\Planet;
use App\Models\PlanetEntity;
use Illuminate\Support\Collection;

class EntityCollection extends Collection
{
	public static function getForPlanet(Planet $planet)
	{
		$items = $planet->entities
			->map(fn(PlanetEntity $item) => EntityFactory::fromModel($item, $planet));

		return new static($items);
	}

	public function getForTypes($types): static
	{
		if (!is_array($types)) {
			$types = [$types];
		}

		return $this->filter(fn(Entity $item) => in_array(Vars::getItemType($item->entityId), $types));
	}

	public function getEntity($entityId): ?Entity
	{
		if (!is_numeric($entityId)) {
			$entityId = Vars::getIdByName($entityId);
		}

		return $this->firstWhere('entityId', $entityId);
	}

	public function getEntityAmount($entityId): int
	{
		$entity = $this->getEntity($entityId);

		return $entity->level ?? 0;
	}
}
