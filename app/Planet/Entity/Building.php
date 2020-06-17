<?php

namespace Xnova\Planet\Entity;

use Xnova\Exceptions\Exception;
use Xnova\Planet;
use Xnova\Planet\Contracts\PlanetBuildingEntityInterface;
use Xnova\Planet\Production;
use Xnova\Vars;

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
		$user = $this->getPlanet()->getUser();
		$planet = $this->getPlanet()->getPlanet();

		$time = parent::getTime();

		$time *= (1 / ($planet->getLevel('robot_factory') + 1));
		$time *= pow(0.5, $planet->getLevel('nano_factory'));
		$time *= $user->bonusValue('time_building');

		return max(1, $time);
	}
}
