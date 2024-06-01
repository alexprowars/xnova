<?php

namespace App\Engine\Entity;

use App\Engine\Vars;
use App\Exceptions\Exception;

class Ship extends Unit
{
	public function getTime(): int
	{
		$time = parent::getTime();
		$time *= $this->planet->user->bonus('time_fleet');

		return max(1, floor($time));
	}

	public function getConsumption(): int
	{
		$shipData = Vars::getUnitData($this->entityId);

		return (int) ceil($shipData['consumption'] * $this->planet->user->bonus('fleet_fuel'));
	}

	public function getSpeed(): int
	{
		$shipData = Vars::getUnitData($this->entityId);
		$user = $this->planet->user;

		$speed = $shipData['speed'] * match ($shipData['type_engine']) {
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
		$shipData = Vars::getUnitData($this->entityId);

		if (!$shipData) {
			return 0;
		}

		return (int) $shipData['capacity'];
	}

	public function getStayConsumption(): int
	{
		$shipData = Vars::getUnitData($this->entityId);

		if (!$shipData) {
			return 0;
		}

		if ($this->planet->user->rpg_meta?->isFuture()) {
			return (int) ceil($shipData['stay'] * 0.9);
		} else {
			return (int) $shipData['stay'];
		}
	}

	public function getInfo(): array
	{
		$shipData = Vars::getUnitData($this->entityId);

		if (!$shipData) {
			throw new Exception('unit does not exist');
		}

		return [
			'id' => $this->entityId,
			'consumption' => $this->getConsumption(),
			'speed' => $this->getSpeed(),
			'stay' => $this->getStayConsumption(),
			'count' => $this->getLevel(),
			'capacity' => $shipData['capacity'] ?? 0,
		];
	}
}
