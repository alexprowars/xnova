<?php

namespace App\Engine\Queue;

use App\Engine\EntityFactory;
use App\Engine\Entity;
use App\Engine\Enums\QueueConstructionType;
use App\Engine\Enums\QueueType;
use App\Engine\Objects\BaseObject;
use App\Engine\QueueManager;
use App\Models;
use App\Models\LogsHistory;

class Unit
{
	public function __construct(protected QueueManager $queue)
	{
	}

	public function add(BaseObject $element, int $count): void
	{
		$planet = $this->queue->getPlanet();
		$user = $this->queue->getUser();

		$entity = EntityFactory::get($element->getId(), 1, $planet);

		if (!$entity->isAvailable() || (!($entity instanceof Entity\Ship) && !($entity instanceof Entity\Defence))) {
			return;
		}

		$buildItems = $this->queue->get(QueueType::SHIPYARD);

		if ($element->getMaxConstructable()) {
			$total = $planet->getLevel($element->getId());

			foreach ($buildItems as $item) {
				if ($item->object_id == $element->getId()) {
					$total += $item->level;
				}
			}

			$count = min($count, max(($element->getMaxConstructable() - $total), 0));
		}

		if ($element->getId() == 502 || $element->getId() == 503) {
			$Missiles = [];
			$Missiles[502] = $planet->getLevel('interceptor_misil');
			$Missiles[503] = $planet->getLevel('interplanetary_misil');

			$maxMissiles = $planet->getLevel('missile_facility') * 10;

			foreach ($buildItems as $item) {
				if (($item->object_id == 502 || $item->object_id == 503) && $item->level != 0) {
					$Missiles[$item->object_id] += $item->level;
				}
			}

			$ActuMissiles 	= $Missiles[502] + (2 * $Missiles[503]);
			$MissilesSpace 	= $maxMissiles - $ActuMissiles;

			if ($MissilesSpace > 0) {
				if ($element->getId() == 502) {
					$count = min($count, $MissilesSpace);
				} else {
					$count = min($count, floor($MissilesSpace / 2));
				}
			} else {
				$count = 0;
			}
		}

		if (!$count) {
			return;
		}

		$count = min($count, $entity->getMaxConstructible());

		if ($count <= 0) {
			return;
		}

		$cost = $entity->getPrice();

		$planet->metal 		-= $cost['metal'] * $count;
		$planet->crystal 	-= $cost['crystal'] * $count;
		$planet->deuterium 	-= $cost['deuterium'] * $count;
		$planet->update();

		$buildTime = $entity->getTime();

		Models\Queue::create([
			'type' => QueueType::SHIPYARD,
			'operation' => QueueConstructionType::BUILDING,
			'user_id' => $user->id,
			'planet_id' => $planet->id,
			'object_id' => $element->getId(),
			'date' => now(),
			'date_end' => now()->addSeconds($buildTime),
			'level' => $count
		]);

		if (config('game.log.units', false)) {
			LogsHistory::create([
				'user_id' 			=> $user->id,
				'operation' 		=> 7,
				'planet' 			=> $planet->id,
				'from_metal' 		=> $planet->metal + $cost['metal'],
				'from_crystal' 		=> $planet->crystal + $cost['crystal'],
				'from_deuterium' 	=> $planet->deuterium + $cost['deuterium'],
				'to_metal' 			=> $planet->metal,
				'to_crystal' 		=> $planet->crystal,
				'to_deuterium' 		=> $planet->deuterium,
				'entity_id' 		=> $element->getId(),
				'amount' 			=> $count
			]);
		}
	}
}
