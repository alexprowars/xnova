<?php

namespace App\Planet\Entity;

use App\Exceptions\Exception;
use App\Vars;

class Ship extends Unit
{
	public function getTime(): int
	{
		$time = parent::getTime();
		$time *= $this->planet->user->bonusValue('time_fleet');

		return max(1, floor($time));
	}

	public function getConsumption(): int
	{
		$shipData = Vars::getUnitData($this->entity_id);

		return (int) ceil($shipData['consumption'] * $this->planet->user->bonusValue('fleet_fuel'));
	}

	public function getSpeed(): int
	{
		$shipData = Vars::getUnitData($this->entity_id);
		$user = $this->planet->user;

		$speed = $shipData['speed'] * match ($shipData['type_engine']) {
			1 => 1 + ($user->getTechLevel('combustion') * 0.1),
			2 => 1 + ($user->getTechLevel('impulse_motor') * 0.2),
			3 => 1 + ($user->getTechLevel('hyperspace_motor') * 0.3),
			default => 1,
		};

		if ($user->bonusValue('fleet_speed') != 1) {
			$speed = $speed * $user->bonusValue('fleet_speed');
		}

		return (int) floor($speed);
	}

	public function getStorage(): int
	{
		$shipData = Vars::getUnitData($this->entity_id);

		if (!$shipData) {
			return 0;
		}

		return (int) $shipData['capacity'];
	}

	public function getStayConsumption(): int
	{
		$shipData = Vars::getUnitData($this->entity_id);

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
		$shipData = Vars::getUnitData($this->entity_id);

		if (!$shipData) {
			throw new Exception('unit does not exist');
		}

		return [
			'id' => $this->entity_id,
			'consumption' => $this->getConsumption(),
			'speed' => $this->getSpeed(),
			'stay' => $this->getStayConsumption(),
			'count' => $this->getLevel(),
			'capacity' => $shipData['capacity'] ?? 0,
		];
	}
}
