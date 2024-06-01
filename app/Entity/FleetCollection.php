<?php

namespace App\Entity;

use App\Models\Planet;
use Illuminate\Support\Collection;
use App\Game;
use App\Engine\Entity\Ship;

class FleetCollection extends Collection
{
	public static function createFromArray(array $items, Planet $planet): static
	{
		return (new static($items))->map(fn($count, $item) => Ship::createEntity($item, $count, $planet));
	}

	public function getSpeed(): int
	{
		return $this->map(function ($item) {
			return $item->getSpeed();
		})->min();
	}

	public function getDistance(Coordinates $origin, Coordinates $destination): int
	{
		$abs = abs($origin->getGalaxy() - $destination->getGalaxy());

		if ($abs != 0) {
			return $abs * 20000;
		}

		$abs = abs($origin->getSystem() - $destination->getSystem());

		if ($abs != 0) {
			return $abs * 95 + 2700;
		}

		$abs = abs($origin->getPlanet() - $destination->getPlanet());

		if ($abs != 0) {
			return $abs * 5 + 1000;
		}

		return 5;
	}

	public function getDuration($speed, $distance): int
	{
		return (int) round(((35000 / $speed) * sqrt($distance * 10 / $this->getSpeed()) + 10) / Game::getSpeed('fleet'));
	}

	public function getConsumption($duration, $distance): int
	{
		$duration = max($duration, 2);

		$consumption = $this->filter(function ($item) {
			return $item->getLevel() > 0;
		})
		->reduce(function ($total, $item) use ($duration, $distance) {
			$speed = 35000 / ($duration * Game::getSpeed('fleet') - 10) * sqrt($distance * 10 / $item->getSpeed());

			return $total + (($item->getConsumption() * $item->getLevel()) * $distance / 35000 * pow(($speed / 10) + 1, 2));
		});

		return (int) round($consumption) + 1;
	}

	public function getStorage(): int
	{
		return $this->reduce(function ($total, $item) {
			return $total + ($item->getStorage() * $item->getLevel());
		});
	}

	public function getStayConsumption(): int
	{
		return $this->reduce(function ($total, $item) {
			return $total + ($item->getStayConsumption() * $item->getLevel());
		});
	}
}
