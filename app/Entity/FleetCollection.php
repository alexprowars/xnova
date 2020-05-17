<?php

namespace Xnova\Entity;

use Xnova\Game;

class FleetCollection
{
	/** @var Fleet[] */
	private $items = [];

	public function __construct(array $items)
	{
		$this->items = $items;
	}

	public static function createFromArray(array $items): self
	{
		$entity = [];

		foreach ($items as $item => $count) {
			$entity[] = new Fleet($item, $count);
		}

		return new self($entity);
	}

	public function getSpeed(): int
	{
		$speeds = [];

		foreach ($this->items as $item) {
			$speeds[] = $item->getSpeed();
		}

		return min($speeds);
	}

	public function getDistance($OrigGalaxy, $DestGalaxy, $OrigSystem, $DestSystem, $OrigPlanet, $DestPlanet): int
	{
		if (($OrigGalaxy - $DestGalaxy) != 0) {
			return abs($OrigGalaxy - $DestGalaxy) * 20000;
		}

		if (($OrigSystem - $DestSystem) != 0) {
			return abs($OrigSystem - $DestSystem) * 95 + 2700;
		}

		if (($OrigPlanet - $DestPlanet) != 0) {
			return abs($OrigPlanet - $DestPlanet) * 5 + 1000;
		}

		return 5;
	}

	public function getDuration($speed, $distance): int
	{
		return (int) round(((35000 / $speed) * sqrt($distance * 10 / $this->getSpeed()) + 10) / Game::getSpeed('fleet'));
	}

	public function getConsumption($duration, $distance): int
	{
		$consumption = 0;

		if ($duration <= 1) {
			$duration = 2;
		}

		foreach ($this->items as $item) {
			if ($item->getLevel() <= 0) {
				continue;
			}

			$speed = 35000 / ($duration * Game::getSpeed('fleet') - 10) * sqrt($distance * 10 / $item->getSpeed());

			$consumption += ($item->getConsumption() * $item->getLevel()) * $distance / 35000 * pow(($speed / 10) + 1, 2);
		}

		return (int) round($consumption) + 1;
	}

	public function getStorage(): int
	{
		$storage = 0;

		foreach ($this->items as $item) {
			$storage += $item->getStorage() * $item->getLevel();
		}

		return $storage;
	}

	public function getStayConsumption(): int
	{
		$consumption = 0;

		foreach ($this->items as $item) {
			$consumption += $item->getStayConsumption() * $item->getLevel();
		}

		return $consumption;
	}
}
