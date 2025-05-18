<?php

namespace App\Engine\Fleet;

use App\Engine\Coordinates;
use App\Engine\Entity\Ship;
use App\Engine\Game;
use App\Models\Planet;
use Illuminate\Support\Collection;

class FleetCollection extends Collection
{
	public static function createFromArray(array $items, Planet $planet): self
	{
		return (new self($items))->map(fn($count, $item) => Ship::createEntity($item, $count, $planet));
	}

	public function getSpeed(): int
	{
		return $this->map(fn(Ship $item) => $item->getSpeed())->min();
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

	public function getDuration(int $speed, int $distance): int
	{
		return (int) round(((35000 / $speed) * sqrt($distance * 10 / $this->getSpeed()) + 10) / Game::getSpeed('fleet'));
	}

	public function getConsumption(int $duration, int $distance): int
	{
		$duration = max($duration, 2);

		$consumption = $this
			->filter(fn(Ship $item) => $item->getLevel() > 0)
			->reduce(function ($total, Ship $item) use ($duration, $distance) {
				$speed = 35000 / ($duration * Game::getSpeed('fleet') - 10) * sqrt($distance * 10 / $item->getSpeed());

				return $total + (($item->getConsumption() * $item->getLevel()) * $distance / 35000 * ((($speed / 10) + 1) ** 2));
			}, 0);

		return max((int) round($consumption), 1);
	}

	public function getStorage(): int
	{
		return $this->reduce(fn(int $total, Ship $item) => $total + ($item->getStorage() * $item->getLevel()), 0);
	}

	public function getStayConsumption(): int
	{
		return $this->reduce(fn(int $total, Ship $item) => $total + ($item->getStayConsumption() * $item->getLevel()), 0);
	}
}
