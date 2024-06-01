<?php

namespace App\Engine\Queue;

use App\Engine\Entity;
use App\Engine\QueueManager;
use App\Models;

class Build
{
	public function __construct(protected QueueManager $queue)
	{
	}

	public function add($elementId, $destroy = false)
	{
		$planet = $this->queue->getPlanet();
		$user = $this->queue->getUser();

		$maxBuidSize = config('settings.maxBuildingQueue', 1);

		if ($user->rpg_constructeur?->isFuture()) {
			$maxBuidSize += 2;
		}

		$actualCount = $this->queue->getCount(QueueManager::TYPE_BUILDING);

		if ($actualCount < $maxBuidSize) {
			$queueID = $actualCount + 1;
		} else {
			$queueID = false;
		}

		$currentMaxFields = $planet->getMaxFields();

		if ($planet->field_current < ($currentMaxFields - $actualCount) || $destroy) {
			if ($queueID > 1) {
				$inArray = 0;

				foreach ($this->queue->get(QueueManager::TYPE_BUILDING) as $item) {
					if ($item->object_id == $elementId) {
						$inArray++;
					}
				}
			} else {
				$inArray = 0;
			}

			$build = $planet->getEntity($elementId);

			if (!$build) {
				return false;
			}

			Models\Queue::create([
				'type' => Models\Queue::TYPE_BUILD,
				'operation' => $destroy ? Models\Queue::OPERATION_DESTROY : Models\Queue::OPERATION_BUILD,
				'user_id' => $user->id,
				'planet_id' => $planet->id,
				'object_id' => $elementId,
				'time' => null,
				'time_end' => null,
				'level' => $build->amount + (!$destroy ? 1 : 0) + $inArray
			]);

			$this->queue->loadQueue();
			$this->queue->nextBuildingQueue();
		}

		return true;
	}

	public function delete($indexId)
	{
		$queueArray = $this->queue->get(QueueManager::TYPE_BUILDING);

		if (empty($queueArray) || empty($queueArray[$indexId])) {
			return;
		}

		$queueItem = $queueArray[$indexId];

		if (!$this->queue->deleteInQueue($queueItem->id)) {
			$queueItem->delete();
		}

		if ($queueItem->time) {
			$planet = $this->queue->getPlanet();

			$entity = Entity\Building::createEntity($queueItem->object_id, $queueItem->level, $planet);

			$cost = $queueItem->operation == $queueItem::OPERATION_DESTROY
				? $entity->getDestroyPrice() : $entity->getPrice();

			$planet->metal 		+= $cost['metal'];
			$planet->crystal 	+= $cost['crystal'];
			$planet->deuterium 	+= $cost['deuterium'];

			$planet->update();
		}

		if (count($queueArray) > 1) {
			unset($queueArray[$indexId]);

			/** @var Models\Queue[] $queueArray */
			$queueArray = array_values($queueArray);

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
