<?php

namespace App\Engine\Queue;

use App\Engine\Enums\QueueConstructionType;
use App\Engine\Enums\QueueType;
use App\Engine\Objects\BaseObject;
use App\Engine\QueueManager;
use App\Engine\Entity;
use App\Models;
use App\Models\LogsHistory;
use App\Models\Planet;

class Tech
{
	public function __construct(private QueueManager $queue)
	{
	}

	public function add(BaseObject $element): void
	{
		$planet = $this->queue->getPlanet();
		$user = $this->queue->getUser();

		$techHandle = Models\Queue::query()
			->whereBelongsTo($user)
			->where('type', QueueType::RESEARCH)
			->exists();

		if (!$techHandle) {
			$entity = Entity\Research::createEntity($element->getId(), $user->getTechLevel($element->getId()), $planet);
			$cost = $entity->getPrice();

			$price = $entity->getObject()->getPrice();

			if ($entity->isAvailable() && $entity->canConstruct() && !(isset($price['max']) && $user->getTechLevel($element->getId()) >= $price['max'])) {
				$planet->metal 		-= $cost['metal'];
				$planet->crystal 	-= $cost['crystal'];
				$planet->deuterium 	-= $cost['deuterium'];
				$planet->update();

				$buildTime = $entity->getTime();

				Models\Queue::create([
					'type' => QueueType::RESEARCH,
					'operation' => QueueConstructionType::BUILDING,
					'user_id' => $user->id,
					'planet_id' => $planet->id,
					'object_id' => $element->getId(),
					'date' => now(),
					'date_end' => now()->addSeconds($buildTime),
					'level' => $user->getTechLevel($element->getId()) + 1,
				]);

				if (config('game.log.research', false)) {
					LogsHistory::create([
						'user_id' 			=> $user->id,
						'operation' 		=> 5,
						'planet' 			=> $planet->id,
						'from_metal' 		=> $planet->metal + $cost['metal'],
						'from_crystal' 		=> $planet->crystal + $cost['crystal'],
						'from_deuterium' 	=> $planet->deuterium + $cost['deuterium'],
						'to_metal' 			=> $planet->metal,
						'to_crystal' 		=> $planet->crystal,
						'to_deuterium' 		=> $planet->deuterium,
						'entity_id' 		=> $element->getId(),
						'amount' 			=> $user->getTechLevel($element->getId()) + 1
					]);
				}
			}
		}
	}

	public function delete(BaseObject $element): void
	{
		$user = $this->queue->getUser();

		$techHandle = $user->queue->firstWhere('type', QueueType::RESEARCH);

		if ($techHandle && $techHandle->object_id == $element->getId()) {
			$planet = Planet::query()
				->find((int) $techHandle->planet_id);

			$entity = Entity\Research::createEntity($element->getId(), $techHandle->level, $planet);

			$cost = $entity->getPrice();

			$planet->metal += $cost['metal'];
			$planet->crystal += $cost['crystal'];
			$planet->deuterium += $cost['deuterium'];
			$planet->update();

			$techHandle->delete();
			$this->queue->loadQueue();

			if (config('game.log.research', false)) {
				LogsHistory::create([
					'user_id' 			=> $user->id,
					'operation' 		=> 6,
					'planet' 			=> $planet->id,
					'from_metal' 		=> $planet->metal - $cost['metal'],
					'from_crystal' 		=> $planet->crystal - $cost['crystal'],
					'from_deuterium' 	=> $planet->deuterium - $cost['deuterium'],
					'to_metal' 			=> $planet->metal,
					'to_crystal' 		=> $planet->crystal,
					'to_deuterium' 		=> $planet->deuterium,
					'entity_id' 		=> $element->getId(),
					'amount' 			=> $user->getTechLevel($element->getId()) + 1
				]);
			}
		}
	}
}
