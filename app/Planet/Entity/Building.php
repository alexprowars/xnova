<?php

namespace Xnova\Planet\Entity;

use Xnova\Exceptions\Exception;
use Xnova\Planet\Contracts\PlanetBuildingEntityInterface;
use Xnova\Planet\Production;
use Xnova\Vars;

class Building extends BaseEntity implements PlanetBuildingEntityInterface
{
	public function __construct($entityId, ?int $level = null, $context = null)
	{
		if (Vars::getItemType($entityId) !== Vars::ITEM_TYPE_BUILING) {
			throw new Exception('wrong entity type');
		}

		if ($level === null) {
			$level = ($context ? $context : $this->getContext())->getPlanet()->getLevel($entityId);
		}

		parent::__construct($entityId, $level, $context);
	}

	protected function getBasePrice(): array
	{
		$cost = parent::getBasePrice();

		$price = Vars::getItemPrice($this->entityId);

		return array_map(function ($value) use ($price) {
			return floor($value * pow($price['factor'] ?? 1, $this->level));
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
		$user = $this->getContext()->getUser();
		$planet = $this->getContext()->getPlanet();

		$time = parent::getTime();

		$time *= (1 / ($planet->getLevel('robot_factory') + 1));
		$time *= pow(0.5, $planet->getLevel('nano_factory'));
		$time *= $user->bonusValue('time_building');

		return max(1, $time);
	}
}
