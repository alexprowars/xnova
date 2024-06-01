<?php

namespace App\Engine\Entity;

use App\Engine\Contracts\EntityBuildingInterface;
use App\Vars;

class Building extends Entity implements EntityBuildingInterface
{
	protected function getBasePrice(): array
	{
		$cost = parent::getBasePrice();

		$price = Vars::getItemPrice($this->entityId);

		return array_map(function ($value) use ($price) {
			return floor($value * (($price['factor'] ?? 1) ** $this->level));
		}, $cost);
	}

	public function getDestroyPrice(): array
	{
		$cost = $this->getPrice();

		return array_map(function ($value) {
			return floor($value / 2);
		}, $cost);
	}

	public function getTime(): int
	{
		$time = parent::getTime();

		$time *= (1 / ($this->planet->getLevel('robot_factory') + 1));
		$time *= 0.5 ** $this->planet->getLevel('nano_factory');
		$time *= $this->planet->user->bonusValue('time_building');

		return max(1, $time);
	}
}
