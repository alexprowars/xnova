<?php

namespace App\Engine\Entity;

use App\Engine\Objects;

class Ship extends Unit
{
	public function getTime(): int
	{
		$time = parent::getTime();
		$time *= $this->planet->user->bonus('time_fleet');

		return max(1, (int) floor($time));
	}

	public function getConsumption(): int
	{
		if (!($this->object instanceof Objects\ShipObject)) {
			return 0;
		}

		return (int) floor($this->object->getConsumption() * $this->planet->user->bonus('fleet_fuel'));
	}

	public function getSpeed(): int
	{
		if (!($this->object instanceof Objects\ShipObject)) {
			return 0;
		}

		$user = $this->planet->user;

		$speed = $this->object->getSpeed() * match ($this->object->getEngineType()) {
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
		if (!($this->object instanceof Objects\ShipObject)) {
			return 0;
		}

		return $this->object->getCapacity();
	}

	public function getStayConsumption(): int
	{
		if (!($this->object instanceof Objects\ShipObject)) {
			return 0;
		}

		if ($this->planet->user->officier_metaphysician?->isFuture()) {
			return (int) ceil($this->object->getStayConsumption() * 0.9);
		}

		return $this->object->getStayConsumption();
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
