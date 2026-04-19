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

		if ($planet->field_current < ($currentMaxFields - $actualCount) || $destroy) {
			if ($queueId > 1) {
				$inArray = 0;

				foreach ($this->queue->get(QueueType::BUILDING) as $item) {
					if ($item->object_id == $element->getId()) {
						$inArray++;
					}
				}
			} else {
				$inArray = 0;
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
				'level' => $build->level + (!$destroy ? 1 : 0) + $inArray
			]);

			$this->queue->loadQueue();
			$this->queue->nextBuildingQueue();
		}
	}

	public function delete(int $indexId): void
	{
		$queueArray = $this->queue->get(QueueType::BUILDING);

		$queueItem = $queueArray->get($indexId);

		if (!$queueItem) {
			return;
		}

		if (!$this->queue->deleteInQueue($queueItem)) {
			$queueItem->delete();
		}

		if ($queueItem->date) {
			$planet = $this->queue->getPlanet();

			$entity = Entity\Building::createEntity($queueItem->object_id, $queueItem->level, $planet);

			$cost = $queueItem->operation == QueueConstructionType::DESTROY
				? $entity->getDestroyPrice() : $entity->getPrice();

			$planet->metal 		+= $cost['metal'];
			$planet->crystal 	+= $cost['crystal'];
			$planet->deuterium 	+= $cost['deuterium'];

			$planet->update();
		}

		if ($queueArray->count() > 1) {
			$queueArray->forget($indexId);
			$queueArray = $queueArray->values();

			foreach ($queueArray as $i => $item) {
				if ($queueItem->object_id == $item->object_id && $indexId <= $i) {
					$item->level--;
					$item->update();
				}
			}
		}

		$this->queue->loadQueue();
		$this->queue->nextBuildingQueue();
	}
}
