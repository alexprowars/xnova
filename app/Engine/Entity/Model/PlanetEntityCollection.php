<?php

namespace App\Engine\Entity\Model;

use Illuminate\Support\Collection;

/**
 * @extends Collection<int, PlanetEntity>
 */
class PlanetEntityCollection extends Collection
{
	public function getByEntityId(int $id): PlanetEntity
	{
		$entity = $this->firstWhere('id', $id);

		if ($entity) {
			return $entity;
		}

		$enitity = PlanetEntity::create($id);

		$this->add($enitity);

		return $enitity;
	}
}
