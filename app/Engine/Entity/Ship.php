<?php

namespace App\Engine\Entity;

use App\Engine\Contracts\EntityUnitInterface;
use App\Engine\Objects\ShipObject;

/**
 * @extends Entity<ShipObject>
 */
class Ship extends Entity implements EntityUnitInterface
{
	use Unit;

	public function getTime(): int
	{
		$time = $this->getBaseTime();
		$time *= $this->planet->user->bonus('time_fleet');

		return max(1, (int) floor($time));
	}

	public function getConsumption(): int
	{
		return (int) floor($this->getObject()->getConsumption() * $this->planet->user->bonus('fleet_fuel'));
	}

	public function getSpeed(): int
	{
		$user = $this->planet->user;

		$speed = $this->getObject()->getSpeed() * match ($this->getObject()->getEngineType()) {
			1 => 1 + ($user->getTechLevel('combustion') * 0.1),
			2 => 1 + ($user->getTechLevel('impulse_motor') * 0.2),
			3 => 1 + ($user->getTechLevel('hyperspace_motor') * 0.3),
			default => 1,
		};

		if ($user->bonus('fleet_speed') != 1) {
			$speed *= $user->bonus('fleet_speed');
		}

		return (int) floor($speed);
	}

	public function getStorage(): int
	{
		return $this->getObject()->getCapacity();
	}

	public function getStayConsumption(): int
	{
		if ($this->planet->user->officier_metaphysician?->isFuture()) {
			return (int) ceil($this->getObject()->getStayConsumption() * 0.9);
		}

		return $this->getObject()->getStayConsumption();
	}

	public function getInfo(): array
	{
		return [
			'id' => $this->entityId,
			'consumption' => $this->getConsumption(),
			'speed' => $this->getSpeed(),
			'stay' => $this->getStayConsumption(),
			'count' => $this->getLevel(),
			'capacity' => $this->getStorage(),
		];
	}
}
