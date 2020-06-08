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
		return new self(PlanetEntity::query()
			->where('planet_id', $planet->id)
			->get());
	}

	public function getForTypes($types): Collection
	{
		if (!is_array($types)) {
			$types = [$types];
		}

		return $this->filter(static function ($item) use ($types) {
			return in_array(Vars::getItemType($item->entity_id), $types);
		});
	}

	public function getEntity($entityId): ?PlanetEntity
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
