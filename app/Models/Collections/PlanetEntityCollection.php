<?php

namespace App\Models\Collections;

use App\Engine\Objects\BaseObject;
use App\Models\PlanetEntity;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends Collection<int, PlanetEntity>
 */
class PlanetEntityCollection extends Collection
{
	public function getByEntityId(BaseObject $object): ?PlanetEntity
	{
		$entity = $this->firstWhere('entity_id', $object->getId());

		if ($entity) {
			return $entity;
		}

		return null;
	}
}
