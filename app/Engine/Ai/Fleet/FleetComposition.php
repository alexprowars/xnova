<?php

namespace App\Engine\Ai\Fleet;

use App\Engine\Ai\Economy\ResourceValue;
use App\Engine\Entity\Ship;
use App\Engine\Enums\ItemType;
use App\Engine\Objects\ObjectsFactory;
use App\Engine\Objects\ShipObject;
use App\Facades\Vars;
use App\Models\Planet;

class FleetComposition
{
	public function __construct(private Planet $planet)
	{
	}

	/** @return array<int, int> */
	public function available(bool $combatOnly = false): array
	{
		$ships = [];

		foreach (Vars::getObjectsByType(ItemType::FLEET) as $object) {
			$id = $object->getId();

			if (!$object instanceof ShipObject || $object->getSpeed() <= 0
				|| ($combatOnly && in_array($id, [208, 209, 210, 216], true))) {
				continue;
			}

			$count = $this->planet->getLevel($id);

			if ($count > 0) {
				$ships[$id] = $count;
			}
		}

		return $ships;
	}

	/** @return list<array<int, int>> */
	public function raids(array $report): array
	{
		$available = $this->available(true);
		$combat = array_diff_key($available, [202 => true, 203 => true]);
		$loot = array_sum(array_intersect_key($report['resources'], ResourceValue::WEIGHTS)) / 2;
		$variants = [];
		$defenderValue = 0.0;
		$combatValue = 0.0;

		foreach ($report['units'] as $id => $count) {
			$defenderValue += ResourceValue::sum(ObjectsFactory::get($id)->getPrice()) * $count;
		}

		foreach ($combat as $id => $count) {
			$combatValue += ResourceValue::sum(ObjectsFactory::get($id)->getPrice()) * $count;
		}

		$fraction = min(1.0, max(0.01, $defenderValue * (float) config('ai.combat_margin', 1.15) / max(1, $combatValue)));

		// Ограниченный набор составов: никаких переборов комбинаций кораблей.
		foreach ([$defenderValue > 0 ? $fraction : 0, min(1.0, $fraction * 2), 1.0] as $fraction) {
			$ships = $fraction > 0 ? array_map(fn(int $count) => max(1, (int) ceil($count * $fraction)), $combat) : [];
			$ships = $this->withCargo($ships, $available, $loot);

			if (!empty($ships)) {
				$variants[json_encode($ships, JSON_THROW_ON_ERROR)] = $ships;
			}
		}

		if (!empty($combat)) {
			$speeds = [];

			foreach ($combat as $id => $count) {
				$speeds[$id] = Ship::createEntity($id, 1, $this->planet)->getSpeed();
			}

			$fast = array_intersect_key($combat, array_filter($speeds, fn(int $speed) => $speed >= max($speeds) * 0.75));
			$fast = $this->withCargo($fast, $available, $loot);
			$variants[json_encode($fast, JSON_THROW_ON_ERROR)] = $fast;
		}

		return array_values($variants);
	}

	private function withCargo(array $ships, array $available, float $loot): array
	{
		$capacity = 0;

		foreach ($ships as $id => $count) {
			$capacity += Ship::createEntity($id, 1, $this->planet)->getStorage() * $count;
		}

		foreach ([203, 202] as $id) {
			$count = min(
				$available[$id] ?? 0,
				max(
					0,
					(int) ceil(($loot * 1.05 - $capacity) / max(1, Ship::createEntity($id, 1, $this->planet)->getStorage())),
				),
			);

			if ($count > 0) {
				$ships[$id] = $count;
				$capacity += Ship::createEntity($id, 1, $this->planet)->getStorage() * $count;
			}
		}

		ksort($ships);

		return $ships;
	}
}
