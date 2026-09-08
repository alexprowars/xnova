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

		$planet->getConnection()
			->transaction(function () use ($planet, $user, $element) {
				$user->refreshForUpdate();

				$planet->refreshForUpdate();
				$planet->setRelation('user', $user);
				$planet->getProduction()->reset();

				$this->addLocked($element);
			});
	}

	protected function addLocked(BaseObject $element): void
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

			if ($entity->isAvailable() && $entity->canConstruct() && !($element->getMaxConstructable() && $user->getTechLevel($element->getId()) >= $element->getMaxConstructable())) {
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

		$user->getConnection()
			->transaction(function () use ($user, $element) {
				$user->refreshForUpdate();

				$this->deleteLocked($element);
			});

		$user->unsetRelation('queue');
		$this->queue->loadQueue();
	}

	protected function deleteLocked(BaseObject $element): void
	{
		$user = $this->queue->getUser();

		$techHandle = $user->queue()
			->where('type', QueueType::RESEARCH)
			->lockForUpdate()
			->first();

		if ($techHandle && $techHandle->object_id == $element->getId()) {
			$planet = Planet::query()
				->lockForUpdate()
				->findOrFail($techHandle->planet_id);

			$planet->setRelation('user', $user);

			$entity = Entity\Research::createEntity($element->getId(), $techHandle->level - 1, $planet);

			$cost = $entity->getPrice();

			$planet->metal += $cost['metal'];
			$planet->crystal += $cost['crystal'];
			$planet->deuterium += $cost['deuterium'];
			$planet->update();

			$techHandle->delete();

			$this->queue->resumeBuildingQueues();

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
