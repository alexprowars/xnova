<?php

namespace App\Planet\Entity;

use App\Planet\Contracts\PlanetUnitEntityInterface;

class Unit extends BaseEntity implements PlanetUnitEntityInterface
{
	public function getTime(): int
	{
		$time = parent::getTime();

		$planet = $this->planet;

		$time *= (1 / ($planet->getLevel('hangar') + 1));
		$time *= pow(1 / 2, $planet->getLevel('nano_factory'));

		return max(1, $time);
	}

	public function getMaxConstructible(): int
	{
		$planet = $this->planet;

		$max = 0;

		$price = parent::getPrice();

		foreach ($price as $type => $count) {
			if (!in_array($type, ['metal', 'crystal', 'deuterium', 'energy']) || $count <= 0) {
				continue;
			}

			$count = floor($planet->{$type} / $count);

			$max = max($max, $count);
		}

		if (isset($price['max']) && $max > $price['max']) {
			$max = $price['max'];
		}

		return (int) $max;
	}
}
