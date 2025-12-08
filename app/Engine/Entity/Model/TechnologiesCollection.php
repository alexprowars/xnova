<?php

namespace App\Engine\Entity\Model;

use Illuminate\Support\Collection;

/**
 * @extends Collection<int, TechnologiesEntity>
 */
class TechnologiesCollection extends Collection
{
	public function getByEntityId(int $id): TechnologiesEntity
	{
		$entity = $this->firstWhere('id', $id);

		if ($entity) {
			return $entity;
		}

		$enitity = TechnologiesEntity::create($id);

		$this->add($enitity);

		return $enitity;
	}
}
