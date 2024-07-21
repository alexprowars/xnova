<?php

namespace App\Engine\Entity;

use App\Engine\Contracts\EntityUnitInterface;
use App\Engine\Enums\Resources;

class Unit extends Entity implements EntityUnitInterface
{
	public function getTime(): int
	{
		$time = parent::getTime();

		$time *= (1 / ($this->planet->getLevel('hangar') + 1));
		$time *= (1 / 2) ** $this->planet->getLevel('nano_factory');

		return max(1, $time);
	}

	public function getMaxConstructible(): int
	{
		$max = 0;

		$price = $this->getPrice();

		foreach ($price as $type => $count) {
			if (!in_array($type, array_column(Resources::cases(), 'value')) || $count <= 0) {
				continue;
			}

			$count = (int) floor($this->planet->{$type} / $count);

			$max = max($max, $count);
		}

		if (isset($price['max']) && $max > $price['max']) {
			$max = (int) $price['max'];
		}

		return $max;
	}
}
