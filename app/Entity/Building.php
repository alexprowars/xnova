<?php

namespace Xnova\Entity;

use Xnova\Exceptions\Exception;
use Xnova\Vars;

class Building extends Base
{
	private $level = 0;

	public function __construct ($elementId, ?int $level = null, $context = null)
	{
		if (Vars::getItemType($elementId) !== Vars::ITEM_TYPE_BUILING)
			throw new Exception('wrong entity type');

		parent::__construct($elementId, $context);

		if (!$level)
			$level = $this->getContext()->getPlanet()->getBuildLevel($this->elementId);

		$this->level = $level;
	}

	public function getBasePrice (): array
	{
		$cost = parent::getBasePrice();

		$price = Vars::getItemPrice($this->elementId);

		return array_map(function ($value) use ($price) {
			return floor($value * pow($price['factor'] ?? 1, $this->level));
		}, $cost);
	}

	public function getDestroyPrice (): array
	{
		$cost = $this->getPrice();

		return array_map(function ($value) {
			return floor($value / 2);
		}, $cost);
	}

	public function getTime (): int
	{
		$user = $this->getContext()->getUser();
		$planet = $this->getContext()->getPlanet();

		$time = parent::getTime();

		$time *= (1 / ($planet->getBuildLevel('robot_factory') + 1));
		$time *= pow(0.5, $planet->getBuildLevel('nano_factory'));
		$time *= $user->bonusValue('time_building');

		return max(1, $time);
	}
}