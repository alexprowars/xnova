<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Coordinates;
use App\Engine\Enums\MessageType;
use App\Engine\Enums\PlanetType;
use App\Engine\Messages\Types\MissionRecyclingMessage;
use App\Models\Fleet;
use App\Models\Planet;
use App\Notifications\SystemMessage;

class Recycling extends BaseMission
{
	public static function isMissionPossible(Planet $planet, Coordinates $target, ?Planet $targetPlanet, array $units = [], bool $isAssault = false): bool
	{
		return $target->getType() == PlanetType::DEBRIS && !empty($units[209]);
	}

	public function targetEvent(): void
	{
		$targetPlanet = Planet::query()
			->coordinates($this->fleet->getDestinationCoordinates(false))
			->whereNot('planet_type', PlanetType::MOON)
			->first();

		$recycled = $this->calculateRecycledGoods($targetPlanet);

		$update = [];

		if (!empty($recycled['metal'])) {
			$update['debris_metal'] = $recycled['metal'];
		}

		if (!empty($recycled['crystal'])) {
			$update['debris_crystal'] = $recycled['crystal'];
		}

		if (!empty($update)) {
			Planet::query()->whereKey($targetPlanet)
				->decrementEach($update);

			Fleet::query()->whereKey($this->fleet)
				->incrementEach([
					'resource_metal' => $recycled['metal'],
					'resource_crystal' => $recycled['crystal'],
				]);
		}

		$this->return();

		$message = new MissionRecyclingMessage([
			'target' => $this->fleet->getDestinationCoordinates()->toArray(),
			'metal' => $recycled['metal'],
			'crystal' => $recycled['crystal'],
		]);

		$this->fleet->user->notify(new SystemMessage(MessageType::Fleet, $message));
	}

	/**
	 * @param Planet|null $target
	 * @return array<'metal'|'crystal', int>
	 */
	protected function calculateRecycledGoods(?Planet $target): array
	{
		$result = [
			'metal' => 0,
			'crystal' => 0,
		];

		if (!$target) {
			return $result;
		}

		$recyclerCapacity = 0;
		$otherFleetCapacity = 0;

		foreach ($this->fleet->entities as $entity) {
			$capacity = $entity->getCapacity();

			if ($entity->id == 209) {
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

		if (($target->debris_metal + $target->debris_crystal) <= $recyclerCapacity) {
			$result['metal'] = $target->debris_metal;
			$result['crystal'] = $target->debris_crystal;
		} elseif (($target->debris_metal > $recyclerCapacity / 2) and ($target->debris_crystal > $recyclerCapacity / 2)) {
			$result['metal'] = $recyclerCapacity / 2;
			$result['crystal'] = $recyclerCapacity / 2;
		} elseif ($target->debris_metal > $target->debris_crystal) {
			$result['crystal'] = $target->debris_crystal;

			if ($target->debris_metal > ($recyclerCapacity - $result['crystal'])) {
				$result['metal'] = $recyclerCapacity - $result['crystal'];
			} else {
				$result['metal'] = $target->debris_metal;
			}
		} else {
			$result['metal'] = $target->debris_metal;

			if ($target->debris_crystal > ($recyclerCapacity - $result['metal'])) {
				$result['crystal'] = $recyclerCapacity - $result['metal'];
			} else {
				$result['crystal'] = $target->debris_crystal;
			}
		}

		return $result;
	}
}
