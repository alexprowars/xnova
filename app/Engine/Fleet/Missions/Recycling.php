<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Coordinates;
use App\Engine\Enums\MessageType;
use App\Engine\Enums\PlanetType;
use App\Engine\Fleet\FleetEngine;
use App\Engine\Vars;
use App\Format;
use App\Models\Planet;
use App\Notifications\MessageNotification;
use Illuminate\Support\Facades\DB;

class Recycling extends FleetEngine implements Mission
{
	public function targetEvent()
	{
		$targetPlanet = Planet::findByCoordinates(
			new Coordinates($this->fleet->end_galaxy, $this->fleet->end_system, $this->fleet->end_planet, PlanetType::MOON)
		);

		if ($targetPlanet) {
			$recyclerCapacity = 0;
			$otherFleetCapacity = 0;

			$storage = Vars::getStorage();

			$fleetData = $this->fleet->getShips();

			foreach ($fleetData as $shipId => $shipArr) {
				$capacity = $storage['CombatCaps'][$shipId]['capacity'] * $shipArr['count'];

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

			if (!empty($recycledGoods['metal'])) {
				$targetPlanet->debris_metal = DB::raw('debris_metal - ' . $recycledGoods['metal']);
			}

			if (!empty($recycledGoods['metal'])) {
				$targetPlanet->debris_crystal = DB::raw('debris_crystal - ' . $recycledGoods['metal']);
			}

			$targetPlanet->save();

			$this->fleet->return([
				'resource_metal' => DB::raw('resource_metal + ' . $recycledGoods['metal']),
				'resource_crystal' => DB::raw('resource_crystal + ' . $recycledGoods['crystal'])
			]);

			$Message = sprintf(__('fleet_engine.sys_recy_gotten'), Format::number($recycledGoods['metal']), __('main.Metal'), Format::number($recycledGoods['crystal']), __('main.Crystal'), $this->fleet->getTargetAdressLink());
		} else {
			$this->fleet->return();

			$Message = sprintf(__('fleet_engine.sys_recy_gotten'), 0, __('main.Metal'), 0, __('main.Crystal'), $this->fleet->getTargetAdressLink());
		}

		$this->fleet->user->notify(new MessageNotification(null, MessageType::Fleet, __('fleet_engine.sys_mess_spy_control'), $Message));
	}

	public function endStayEvent()
	{
	}

	public function returnEvent()
	{
		$this->restoreFleetToPlanet();
		$this->killFleet();
	}
}
