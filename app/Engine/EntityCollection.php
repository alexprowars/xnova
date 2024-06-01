<?php

namespace App\Engine;

use App\Models\PlanetEntity;
use Illuminate\Support\Collection;

class EntityCollection extends Collection
{
	public function getForTypes($types): static
	{
		if (!is_array($types)) {
			$types = [$types];
		}

		return $this->filter(fn(PlanetEntity $item) => in_array(Vars::getItemType($item->entity_id), $types));
	}

	public function getEntity($entityId): ?PlanetEntity
	{
		if (!is_numeric($entityId)) {
			$entityId = Vars::getIdByName($entityId);
		}

		return $this->firstWhere('entity_id', $entityId);
	}

	public function getEntityAmount($entityId): int
	{
		$entity = $this->getEntity($entityId);

		return $entity?->amount ?? 0;
	}
}
