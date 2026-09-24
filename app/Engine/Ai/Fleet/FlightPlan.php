<?php

namespace App\Engine\Ai\Fleet;

use App\Engine\Ai\Economy\ResourceValue;
use App\Engine\Coordinates;
use App\Engine\Fleet\FleetCollection;
use App\Engine\Fleet\MissionType;
use App\Engine\Game;
use App\Models\Planet;

readonly class FlightPlan
{
	public int $duration;
	public int $fuel;
	public int $capacity;
	public float $cost;

	public function __construct(
		Planet $origin,
		public Coordinates $target,
		public array $ships,
		public int $speed = 10,
	)
	{
		$fleet = FleetCollection::createFromArray($ships, $origin);
		$distance = $fleet->getDistance($origin->coordinates, $target);
		$this->duration = $fleet->getDuration($speed, $distance);
		$this->fuel = $fleet->getConsumption($this->duration, $distance);
		$this->capacity = $fleet->getStorage() - $this->fuel;
		$cost = 0.0;

		foreach ($fleet as $ship) {
			$cost += ResourceValue::sum($ship->getPrice()) * $ship->getLevel();
		}

		$this->cost = $cost;
	}

	public function isAffordable(Planet $origin, MissionType $mission, float $fuelFraction = 1.0): bool
	{
		return $this->capacity >= 0 && $this->fuel <= $origin->deuterium * $fuelFraction
			&& $this->duration <= self::maxDuration($mission);
	}

	public function profit(array $report, float $loss = 0, ?int $survivingCapacity = null): float
	{
		$capacity = $survivingCapacity === null ? $this->capacity : $survivingCapacity - $this->fuel;

		return ResourceValue::loot($report['resources'], $capacity) - $loss - ResourceValue::sum(['deuterium' => $this->fuel]);
	}

	public static function maxDuration(MissionType $mission): float
	{
		$key = $mission === MissionType::Colonization ? 'max_colonization_flight_hours' : 'max_flight_hours';

		return (float) config('ai.' . $key) * 3600 / max(1, Game::getSpeed('fleet'));
	}
}
