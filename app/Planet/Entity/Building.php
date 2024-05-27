<?php

namespace App\Planet\Entity;

use App\Planet\Contracts\PlanetBuildingEntityInterface;
use App\Vars;

class Building extends BaseEntity implements PlanetBuildingEntityInterface
{
	protected function getBasePrice(): array
	{
		$cost = parent::getBasePrice();

		$price = Vars::getItemPrice($this->entity_id);

		return array_map(function ($value) use ($price) {
			return floor($value * pow($price['factor'] ?? 1, $this->amount));
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
		$time *= pow(0.5, $this->planet->getLevel('nano_factory'));
		$time *= $this->planet->user->bonusValue('time_building');

		return max(1, $time);
	}
}
