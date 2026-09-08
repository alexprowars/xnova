<?php

namespace App\Engine\Queue;

use App\Engine\Entity;
use App\Engine\Enums\QueueConstructionType;
use App\Engine\Enums\QueueType;
use App\Engine\Objects\BaseObject;
use App\Engine\QueueManager;
use App\Models;

class Build
{
	public function __construct(protected QueueManager $queue)
	{
	}

	public function add(BaseObject $element, bool $destroy = false): void
	{
		if ($destroy && in_array($element->getId(), [33, 41], true)) {
			return;
		}

		$planet = $this->queue->getPlanet();
		$user = $this->queue->getUser();

		$maxBuidSize = config('game.maxBuildingQueue', 1);

		if ($user->officier_architect?->isFuture()) {
			$maxBuidSize += 2;
		}

		$actualCount = $this->queue->getCount(QueueType::BUILDING);

		if ($actualCount < $maxBuidSize) {
			$queueId = $actualCount + 1;
		} else {
			$queueId = false;
		}

		$currentMaxFields = $planet->getMaxFields();

		$reservedFields = $this->queue->get(QueueType::BUILDING)
			->where('operation', QueueConstructionType::BUILDING)
			->count();

		if ($planet->field_current < ($currentMaxFields - $reservedFields) || $destroy) {
			$queuedLevelChange = 0;

			if ($queueId > 1) {
				foreach ($this->queue->get(QueueType::BUILDING) as $item) {
					if ($item->object_id == $element->getId()) {
						$queuedLevelChange += $item->operation == QueueConstructionType::DESTROY ? -1 : 1;
					}
				}
			}

			$build = $planet->getEntity($element->getId());

			if (!$build) {
				return;
			}

			Models\Queue::create([
				'type' => QueueType::BUILDING,
				'operation' => $destroy ? QueueConstructionType::DESTROY : QueueConstructionType::BUILDING,
				'user_id' => $user->id,
				'planet_id' => $planet->id,
				'object_id' => $element->getId(),
				'date' => null,
				'date_end' => null,
				'level' => $build->amount + (!$destroy ? 1 : 0) + $queuedLevelChange
			]);

			$this->queue->loadQueue();
			$this->queue->nextBuildingQueue();
		}
	}

	public function delete(int $queueId): void
	{
		$queueArray = $this->queue->get(QueueType::BUILDING);

		$queueItem = $queueArray->firstWhere('id', $queueId);

		if (!$queueItem) {
			return;
		}

		if (!$this->queue->deleteInQueue($queueItem)) {
			$queueItem->delete();
		}

		if ($queueItem->date) {
			$planet = $this->queue->getPlanet();

			$entity = Entity\Building::createEntity(
				$queueItem->object_id,
				$queueItem->level - ($queueItem->operation == QueueConstructionType::BUILDING ? 1 : 0),
				$planet
			);

			$cost = $queueItem->operation == QueueConstructionType::DESTROY
				? $entity->getDestroyPrice() : $entity->getPrice();

			$planet->metal 		+= $cost['metal'];
			$planet->crystal 	+= $cost['crystal'];
			$planet->deuterium 	+= $cost['deuterium'];

			$planet->update();
		}

		if ($queueArray->count() > 1) {
			$queueArray = $queueArray->reject(fn (Models\Queue $item): bool => $item->is($queueItem));

			foreach ($queueArray as $item) {
				if ($queueItem->object_id == $item->object_id && $queueItem->id < $item->id) {
					$item->level += $queueItem->operation == QueueConstructionType::DESTROY ? 1 : -1;
					$item->update();
				}
			}
		}

		$this->queue->loadQueue();
		$this->queue->nextBuildingQueue();
	}
}
