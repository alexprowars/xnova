<?php

namespace App\Queue;

use App\Models\LogHistory;
use App\Engine\EntityFactory;
use App\Queue;
use App\Vars;
use App\Models;

class Unit
{
	private $queue;

	public function __construct(Queue $queue)
	{
		$this->queue = $queue;
	}

	public function add($elementId, $count)
	{
		$planet = $this->queue->getPlanet();
		$user = $this->queue->getUser();

		$entity = EntityFactory::get($elementId, 1, $planet);

		if (!$entity->isAvailable()) {
			return;
		}

		$buildItems = $this->queue->get(Queue::TYPE_SHIPYARD);

		if ($elementId == 502 || $elementId == 503) {
			$Missiles = [];
			$Missiles[502] = $planet->getLevel('interceptor_misil');
			$Missiles[503] = $planet->getLevel('interplanetary_misil');

			$maxMissiles = $planet->getLevel('missile_facility') * 10;

			foreach ($buildItems as $item) {
				if (($item->object_id == 502 || $item->object_id == 503) && $item->level != 0) {
					$Missiles[$item->object_id] += $item->level;
				}
			}
		}

		$price = Vars::getItemPrice($elementId);

		if (isset($price['max'])) {
			$total = $planet->getLevel($elementId);

			foreach ($buildItems as $item) {
				if ($item->object_id == $elementId) {
					$total += $item->level;
				}
			}

			$count = min($count, max(($price['max'] - $total), 0));
		}

		if (($elementId == 502 || $elementId == 503) && isset($Missiles) && isset($maxMissiles)) {
			$ActuMissiles 	= $Missiles[502] + (2 * $Missiles[503]);
			$MissilesSpace 	= $maxMissiles - $ActuMissiles;

			if ($MissilesSpace > 0) {
				if ($elementId == 502) {
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

		if ($count > 0) {
			$cost = $entity->getPrice();

			$planet->metal 		-= $cost['metal'];
			$planet->crystal 	-= $cost['crystal'];
			$planet->deuterium 	-= $cost['deuterium'];
			$planet->update();

			$buildTime = $entity->getTime();

			Models\Queue::create([
				'type' => Models\Queue::TYPE_UNIT,
				'operation' => Models\Queue::OPERATION_BUILD,
				'user_id' => $user->id,
				'planet_id' => $planet->id,
				'object_id' => $elementId,
				'time' => now(),
				'time_end' => now()->addSeconds($buildTime),
				'level' => $count
			]);

			if (config('settings.log.units', false)) {
				LogHistory::create([
					'user_id' 			=> $user->id,
					'operation' 		=> 7,
					'planet' 			=> $planet->id,
					'from_metal' 		=> $planet->metal + $cost['metal'],
					'from_crystal' 		=> $planet->crystal + $cost['crystal'],
					'from_deuterium' 	=> $planet->deuterium + $cost['deuterium'],
					'to_metal' 			=> $planet->metal,
					'to_crystal' 		=> $planet->crystal,
					'to_deuterium' 		=> $planet->deuterium,
					'entity_id' 		=> $elementId,
					'amount' 			=> $count
				]);
			}
		}
	}
}
