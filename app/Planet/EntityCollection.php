<?php

namespace Xnova\Planet;

use Illuminate\Support\Collection;
use Xnova\Models\PlanetEntity;
use Xnova\Planet;
use Xnova\Vars;

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

		return new self($items);
	}

	public function getForTypes($types): Collection
	{
		if (!is_array($types)) {
			$types = [$types];
		}

		return $this->filter(function ($item) use ($types) {
			return in_array(Vars::getItemType($item->entity_id), $types);
		});
	}

	public function getEntity($entityId): ?Planet\Entity\BaseEntity
	{
		if (!is_numeric($entityId)) {
			$entityId = Vars::getIdByName($entityId);
		}

		return $this->firstWhere('entity_id', $entityId) ?? null;
	}

	public function getEntityAmount($entityId): int
	{
		$entity = $this->getEntity($entityId);

		if (!$entity) {
			return 0;
		}

		return (int) $entity->amount;
	}
}
