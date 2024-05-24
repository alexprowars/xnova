<?php

namespace App\Planet;

use Illuminate\Support\Collection;
use App\Models\PlanetEntity;
use App\Models\Planet;
use App\Vars;

class EntityCollection extends Collection
{
	public static function getForPlanet(Planet $planet)
	{
		$items = PlanetEntity::query()
			->where('planet_id', $planet->id)
			->get()
			->map(function (PlanetEntity $item) use ($planet) {
				return EntityFactory::createFromModel($item, $planet);
			});

		return new static($items);
	}

	public function getForTypes($types): static
	{
		if (!is_array($types)) {
			$types = [$types];
		}

		return $this->filter(fn($item) => in_array(Vars::getItemType($item->entity_id), $types));
	}

	public function getEntity($entityId): ?Planet\Entity\BaseEntity
	{
		if (!is_numeric($entityId)) {
			$entityId = Vars::getIdByName($entityId);
		}

		return $this->firstWhere('entity_id', $entityId);
	}

	public function getEntityAmount($entityId): int
	{
		$entity = $this->getEntity($entityId);

		if (!$entity) {
			return 0;
		}

		return $entity->amount;
	}
}
