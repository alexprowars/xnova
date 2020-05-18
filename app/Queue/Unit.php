<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace Xnova\Queue;

use Xnova\Models\LogHistory;
use Xnova\Queue;
use Xnova\Vars;
use Xnova\Models;
use Xnova\Entity;

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

		$context = new Entity\Context($user, $planet);

		if (Vars::getItemType($elementId) === Vars::ITEM_TYPE_DEFENSE) {
			$entity = new Entity\Defence($elementId, 1, $context);
		} else {
			$entity = new Entity\Fleet($elementId, 1, $context);
		}

		if (!$entity->isAvailable()) {
			return;
		}

		$buildItems = $this->queue->get(Queue::TYPE_SHIPYARD);

		if ($elementId == 502 || $elementId == 503) {
			$Missiles = [];
			$Missiles[502] = $planet->getUnitCount('interceptor_misil');
			$Missiles[503] = $planet->getUnitCount('interplanetary_misil');

			$maxMissiles = $planet->getBuildLevel('missile_facility') * 10;

			foreach ($buildItems as $item) {
				if (($item->object_id == 502 || $item->object_id == 503) && $item->level != 0) {
					$Missiles[$item->object_id] += $item->level;
				}
			}
		}

		$price = Vars::getItemPrice($elementId);

		if (isset($price['max'])) {
			$total = $planet->getUnitCount($elementId);

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

			Models\Queue::query()->create([
				'type' => Models\Queue::TYPE_UNIT,
				'operation' => Models\Queue::OPERATION_BUILD,
				'user_id' => $user->getId(),
				'planet_id' => $planet->id,
				'object_id' => $elementId,
				'time' => time(),
				'time_end' => $buildTime + time(),
				'level' => $count
			]);

			if (config('game.log.units', false) == true) {
				LogHistory::query()->insert([
					'user_id' 			=> $user->id,
					'time' 				=> time(),
					'operation' 		=> 7,
					'planet' 			=> $planet->id,
					'from_metal' 		=> $planet->metal + $cost['metal'],
					'from_crystal' 		=> $planet->crystal + $cost['crystal'],
					'from_deuterium' 	=> $planet->deuterium + $cost['deuterium'],
					'to_metal' 			=> $planet->metal,
					'to_crystal' 		=> $planet->crystal,
					'to_deuterium' 		=> $planet->deuterium,
					'build_id' 			=> $elementId,
					'count' 			=> $count
				]);
			}
		}
	}
}
