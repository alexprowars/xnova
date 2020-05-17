<?php

namespace Xnova\Entity;

use Xnova\Exceptions\Exception;
use Xnova\Vars;

class Fleet extends Unit
{
	public function __construct($elementId, $count = 1, $context = null)
	{
		if (Vars::getItemType($elementId) !== Vars::ITEM_TYPE_FLEET) {
			throw new Exception('wrong entity type');
		}

		parent::__construct($elementId, $count, $context);
	}

	public function getTime(): int
	{
		$user = $this->getContext()->getUser();

		$time = parent::getTime();
		$time *= $user->bonusValue('time_fleet');

		return max(1, floor($time));
	}

	public function getConsumption(): int
	{
		$shipData = Vars::getUnitData($this->elementId);

		return (int) ceil($shipData['consumption'] * $this->getContext()->getUser()->bonusValue('fleet_fuel'));
	}

	public function getSpeed(): int
	{
		$shipData = Vars::getUnitData($this->elementId);
		$user = $this->getContext()->getUser();

		switch ($shipData['type_engine']) {
			case 1:
				$speed = $shipData['speed'] * (1 + ($user->getTechLevel('combustion') * 0.1));
				break;
			case 2:
				$speed = $shipData['speed'] * (1 + ($user->getTechLevel('impulse_motor') * 0.2));
				break;
			case 3:
				$speed = $shipData['speed'] * (1 + ($user->getTechLevel('hyperspace_motor') * 0.3));
				break;
			default:
				$speed = $shipData['speed'];
		}

		if ($user->bonusValue('fleet_speed') != 1) {
			$speed = $speed * $user->bonusValue('fleet_speed');
		}

		return (int) floor($speed);
	}

	public function getStorage(): int
	{
		$shipData = Vars::getUnitData($this->elementId);

		if (!$shipData) {
			return 0;
		}

		return (int) $shipData['capacity'];
	}

	public function getStayConsumption(): int
	{
		$shipData = Vars::getUnitData($this->elementId);

		if (!$shipData) {
			return 0;
		}

		if ($this->getContext()->getUser()->rpg_meta > time()) {
			return (int) ceil($shipData['stay'] * 0.9);
		} else {
			return (int) $shipData['stay'];
		}
	}

	public function getInfo(): array
	{
		$shipData = Vars::getUnitData($this->elementId);

		if (!$shipData) {
			throw new Exception('unit does not exist');
		}

		$ship = [
			'id' => $this->elementId,
			'consumption' => $this->getConsumption(),
			'speed' => $this->getSpeed(),
			'stay' => $this->getStayConsumption(),
			'count' => $this->getLevel(),
			'capacity' => $shipData['capacity'] ?? 0,
		];

		return $ship;
	}
}
