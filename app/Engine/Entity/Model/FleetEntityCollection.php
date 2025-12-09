<?php

namespace App\Engine\Entity\Model;

use Illuminate\Support\Collection;

/**
 * @extends Collection<int, FleetEntity>
 */
class FleetEntityCollection extends Collection
{
	public static function createFromArray(array $data): self
	{
		$result = new self();

		foreach ($data as $id => $count) {
			if (!is_numeric($id) || empty($count)) {
				continue;
			}

			$result->add(FleetEntity::create($id, $count));
		}

		return $result;
	}

	public function getByEntityId(int $id): ?FleetEntity
	{
		return $this->firstWhere('id', $id);
	}

	public function getTotal(): int
	{
		return $this->sum('count');
	}
}
