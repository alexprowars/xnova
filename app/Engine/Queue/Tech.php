<?php

namespace App\Engine\Queue;

use App\Engine\QueueManager;
use App\Engine\Vars;
use App\Engine\Entity;
use App\Models;
use App\Models\LogHistory;
use App\Models\Planet;

class Tech
{
	private $queue;

	public function __construct(QueueManager $queue)
	{
		$this->queue = $queue;
	}

	public function add($elementId)
	{
		$planet = $this->queue->getPlanet();
		$user = $this->queue->getUser();

		$techHandle = Models\Queue::query()
			->where('user_id', $user->id)
			->where('type', Models\Queue::TYPE_TECH)
			->exists();

		if (!$techHandle) {
			$entity = Entity\Research::createEntity($elementId, $user->getTechLevel($elementId), $planet);
			$cost = $entity->getPrice();

			$price = Vars::getItemPrice($elementId);

			if ($entity->isAvailable() && $entity->canConstruct() && !(isset($price['max']) && $user->getTechLevel($elementId) >= $price['max'])) {
				$planet->metal 		-= $cost['metal'];
				$planet->crystal 	-= $cost['crystal'];
				$planet->deuterium 	-= $cost['deuterium'];
				$planet->update();

				$buildTime = $entity->getTime();

				Models\Queue::create([
					'type' => Models\Queue::TYPE_TECH,
					'operation' => Models\Queue::OPERATION_BUILD,
					'user_id' => $user->id,
					'planet_id' => $planet->id,
					'object_id' => $elementId,
					'time' => now(),
					'time_end' => now()->addSeconds($buildTime),
					'level' => $user->getTechLevel($elementId) + 1,
				]);

				if (config('settings.log.research', false)) {
					LogHistory::create([
						'user_id' 			=> $user->id,
						'operation' 		=> 5,
						'planet' 			=> $planet->id,
						'from_metal' 		=> $planet->metal + $cost['metal'],
						'from_crystal' 		=> $planet->crystal + $cost['crystal'],
						'from_deuterium' 	=> $planet->deuterium + $cost['deuterium'],
						'to_metal' 			=> $planet->metal,
						'to_crystal' 		=> $planet->crystal,
						'to_deuterium' 		=> $planet->deuterium,
						'entity_id' 		=> $elementId,
						'amount' 			=> $user->getTechLevel($elementId) + 1
					]);
				}
			}
		}
	}

	public function delete($elementId)
	{
		$user = $this->queue->getUser();

		$techHandle = Models\Queue::query()
			->where('user_id', $user->id)
			->where('type', Models\Queue::TYPE_TECH)->first();

		if ($techHandle && $techHandle->object_id == $elementId) {
			$planet = Planet::query()
				->find((int) $techHandle->planet_id);

			$entity = Entity\Research::createEntity($elementId, $techHandle->level, $planet);

			$cost = $entity->getPrice();

			$planet->metal += $cost['metal'];
			$planet->crystal += $cost['crystal'];
			$planet->deuterium += $cost['deuterium'];
			$planet->update();

			$techHandle->delete();
			$this->queue->loadQueue();

			if (config('settings.log.research', false)) {
				LogHistory::create([
					'user_id' 			=> $user->id,
					'operation' 		=> 6,
					'planet' 			=> $planet->id,
					'from_metal' 		=> $planet->metal - $cost['metal'],
					'from_crystal' 		=> $planet->crystal - $cost['crystal'],
					'from_deuterium' 	=> $planet->deuterium - $cost['deuterium'],
					'to_metal' 			=> $planet->metal,
					'to_crystal' 		=> $planet->crystal,
					'to_deuterium' 		=> $planet->deuterium,
					'entity_id' 		=> $elementId,
					'amount' 			=> $user->getTechLevel($elementId) + 1
				]);
			}
		}
	}
}
