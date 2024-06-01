<?php

namespace App\Engine\Entity;

use App\Engine\Contracts\EntityBuildingInterface;
use App\Engine\Vars;

class Building extends Entity implements EntityBuildingInterface
{
	protected function getBasePrice(): array
	{
		$cost  = parent::getBasePrice();
		$price = Vars::getItemPrice($this->entityId);

		return array_map(
			fn ($value) => floor($value * (($price['factor'] ?? 1) ** $this->level)),
			$cost
		);
	}

	public function getDestroyPrice(): array
	{
		return array_map(fn($value) => floor($value / 2), $this->getPrice());
	}

	public function getTime(): int
	{
		$time = parent::getTime();

		$time *= (1 / ($this->planet->getLevel('robot_factory') + 1));
		$time *= 0.5 ** $this->planet->getLevel('nano_factory');
		$time *= $this->planet->user->bonus('time_building');

		return (int) max(1, $time);
	}
}
