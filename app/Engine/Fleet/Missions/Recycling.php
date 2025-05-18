<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Coordinates;
use App\Engine\Enums\MessageType;
use App\Engine\Enums\PlanetType;
use App\Facades\Vars;
use App\Format;
use App\Models\Fleet;
use App\Models\Planet;
use App\Notifications\MessageNotification;

class Recycling extends BaseMission
{
	public function isMissionPossible(Planet $planet, Coordinates $target, ?Planet $targetPlanet, array $units = [], bool $isAssault = false): bool
	{
		return $target->getType() == PlanetType::DEBRIS && !empty($units[209]);
	}

	public function targetEvent()
	{
		$targetPlanet = Planet::findByCoordinates(
			new Coordinates($this->fleet->end_galaxy, $this->fleet->end_system, $this->fleet->end_planet, PlanetType::MOON)
		);

		$recycledGoods = [
			'metal' => 0,
			'crystal' => 0,
		];

		if ($targetPlanet) {
			$recyclerCapacity = 0;
			$otherFleetCapacity = 0;

			$fleetData = $this->fleet->getShips();

			foreach ($fleetData as $shipId => $shipArr) {
				$unitData = Vars::getUnitData($shipId);

				$capacity = $unitData['capacity'] * $shipArr['count'];

				if ($shipId == 209) {
					$recyclerCapacity += $capacity;
				} else {
					$otherFleetCapacity += $capacity;
				}
			}

			$incomingFleetGoods = $this->fleet->resource_metal + $this->fleet->resource_crystal + $this->fleet->resource_deuterium;

			// Если часть ресурсов хранится в переработчиках
			if ($incomingFleetGoods > $otherFleetCapacity) {
				$recyclerCapacity -= ($incomingFleetGoods - $otherFleetCapacity);
			}

			$recycledGoods = [];

			if (($targetPlanet->debris_metal + $targetPlanet->debris_crystal) <= $recyclerCapacity) {
				$recycledGoods['metal'] = $targetPlanet->debris_metal;
				$recycledGoods['crystal'] = $targetPlanet->debris_crystal;
			} elseif (($targetPlanet->debris_metal > $recyclerCapacity / 2) and ($targetPlanet->debris_crystal > $recyclerCapacity / 2)) {
				$recycledGoods['metal'] = $recyclerCapacity / 2;
				$recycledGoods['crystal'] = $recyclerCapacity / 2;
			} elseif ($targetPlanet->debris_metal > $targetPlanet->debris_crystal) {
				$recycledGoods['crystal'] = $targetPlanet->debris_crystal;

				if ($targetPlanet->debris_metal > ($recyclerCapacity - $recycledGoods['crystal'])) {
					$recycledGoods['metal'] = $recyclerCapacity - $recycledGoods['crystal'];
				} else {
					$recycledGoods['metal'] = $targetPlanet->debris_metal;
				}
			} else {
				$recycledGoods['metal'] = $targetPlanet->debris_metal;

				if ($targetPlanet->debris_crystal > ($recyclerCapacity - $recycledGoods['metal'])) {
					$recycledGoods['crystal'] = $recyclerCapacity - $recycledGoods['metal'];
				} else {
					$recycledGoods['crystal'] = $targetPlanet->debris_crystal;
				}
			}

			$update = [];

			if (!empty($recycledGoods['metal'])) {
				$update['debris_metal'] = $recycledGoods['metal'];
			}

			if (!empty($recycledGoods['crystal'])) {
				$update['debris_crystal'] = $recycledGoods['crystal'];
			}

			Planet::query()->whereKey($targetPlanet)
				->decrementEach($update);

			Fleet::query()->whereKey($this->fleet)
				->incrementEach([
					'resource_metal' => $recycledGoods['metal'],
					'resource_crystal' => $recycledGoods['crystal'],
				]);
		}

		$this->return();

		$message = __('fleet_engine.sys_recy_gotten', [
			'm' => Format::number($recycledGoods['metal']),
			'mt' => __('main.metal'),
			'c' => Format::number($recycledGoods['crystal']),
			'ct' => __('main.crystal'),
			'target' => $this->fleet->getTargetAdressLink(),
		]);

		$this->fleet->user->notify(new MessageNotification(null, MessageType::Fleet, __('fleet_engine.sys_mess_spy_control'), $message));
	}
}
