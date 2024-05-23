<?php

namespace App\Queue;

use App\Queue;
use App\Models;
use App\Planet\Entity;

class Build
{
	private $queue;

	public function __construct(Queue $queue)
	{
		$this->queue = $queue;
	}

	public function add($elementId, $destroy = false)
	{
		$planet = $this->queue->getPlanet();
		$user = $this->queue->getUser();

		$maxBuidSize = config('settings.maxBuildingQueue', 1);

		if ($user->rpg_constructeur > time()) {
			$maxBuidSize += 2;
		}

		$actualCount = $this->queue->getCount(Queue::TYPE_BUILDING);

		if ($actualCount < $maxBuidSize) {
			$queueID = $actualCount + 1;
		} else {
			$queueID = false;
		}

		$currentMaxFields = $planet->getMaxFields();

		if ($planet->field_current < ($currentMaxFields - $actualCount) || $destroy) {
			if ($queueID > 1) {
				$inArray = 0;

				foreach ($this->queue->get(Queue::TYPE_BUILDING) as $item) {
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

			Models\Queue::query()->create([
				'type' => Models\Queue::TYPE_BUILD,
				'operation' => $destroy ? Models\Queue::OPERATION_DESTROY : Models\Queue::OPERATION_BUILD,
				'user_id' => $user->getId(),
				'planet_id' => $planet->id,
				'object_id' => $elementId,
				'time' => 0,
				'time_end' => 0,
				'level' => $build->amount + (!$destroy ? 1 : 0) + $inArray
			]);

			$this->queue->loadQueue();
			$this->queue->nextBuildingQueue();
		}

		return true;
	}

	public function delete($indexId)
	{
		$planet = $this->queue->getPlanet();
		$user = $this->queue->getUser();

		if ($this->queue->getCount(Queue::TYPE_BUILDING)) {
			$queueArray = $this->queue->get(Queue::TYPE_BUILDING);

			if (!isset($queueArray[$indexId])) {
				return;
			}

			$buildItem = $queueArray[$indexId];

			if (!$this->queue->deleteInQueue($buildItem->id)) {
				$buildItem->delete();
			}

			if ($buildItem->time > 0) {
				$entity = Entity\Building::createEntity($buildItem->object_id, $buildItem->level, $planet);

				$cost = $buildItem->operation == $buildItem::OPERATION_DESTROY
					? $entity->getDestroyPrice()
					: $entity->getPrice();

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
					if ($buildItem->object_id == $item->object_id && $indexId <= $i) {
						$item->level--;
						$item->update();
					}
				}
			}

			$this->queue->loadQueue();
			$this->queue->nextBuildingQueue();
		}
	}
}
