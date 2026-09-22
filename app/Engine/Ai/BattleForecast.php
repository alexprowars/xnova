<?php

namespace App\Engine\Ai;

use App\Engine\Battle\Battle;
use App\Engine\Entity\Model\FleetEntityCollection;
use App\Engine\Entity\Ship;
use App\Models\Fleet;
use App\Models\Planet;
use App\Models\User;

class BattleForecast
{
	/** @return array{loss: float, capacity: int}|null */
	public function evaluate(Planet $origin, Planet $target, array $ships, array $report): ?array
	{
		$defender = new User(['id' => $target->user_id, 'username' => $target->user->username]);

		foreach ([109, 110, 111, 120, 121, 122] as $id) {
			$level = $report['technologies_known'] ? ($report['technologies'][$id] ?? 0) : $origin->user->getTechLevel($id) + 2;
			$defender->technologies->getByEntityId($id)->setLevel($level);
		}

		// Запас на подкрепления после разведки; скрытые технологии не читаем из БД.
		$defenders = array_map(fn(int $count) => (int) ceil($count * (float) config('ai.combat_margin', 1.15)), $report['units']);
		$maxLoss = 0.0;
		$minCapacity = PHP_INT_MAX;

		for ($i = 0; $i < 2; $i++) {
			$battle = new Battle();
			$battle->addAttackerFleet($this->makeFleet(1, $origin, $origin->user, $ships));
			$battle->addDefenderFleet($this->makeFleet(2, $target, $defender, $defenders));
			$result = $battle->run();

			if (!$result->attackerHasWin()) {
				return null;
			}

			$survivors = $result->getAttackersResultUnits()->getPlayer($origin->user_id)?->getFleet(1);
			$loss = 0.0;
			$capacity = 0;

			foreach ($ships as $id => $count) {
				$remaining = $survivors?->getUnit($id)?->getCount() ?? 0;
				$entity = Ship::createEntity($id, 1, $origin);
				$price = $entity->getPrice();
				$loss += ($count - $remaining) * ($price['metal'] + $price['crystal'] * 1.5 + $price['deuterium'] * 2);
				$capacity += $remaining * $entity->getStorage();
			}

			$maxLoss = max($maxLoss, $loss);
			$minCapacity = min($minCapacity, $capacity);
		}

		return ['loss' => $maxLoss, 'capacity' => $minCapacity];
	}

	private function makeFleet(int $id, Planet $planet, User $user, array $ships): Fleet
	{
		$fleet = new Fleet([
			'id' => $id,
			'entities' => FleetEntityCollection::createFromArray($ships),
			'start_galaxy' => $planet->galaxy,
			'start_system' => $planet->system,
			'start_planet' => $planet->planet,
			'end_galaxy' => $planet->galaxy,
			'end_system' => $planet->system,
			'end_planet' => $planet->planet,
		]);
		$fleet->setRelation('user', $user);

		return $fleet;
	}
}
