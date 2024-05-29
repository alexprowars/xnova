<?php

namespace App;

use App\Planet\EntityFactory;
use App\Models\User;
use App\Models\Planet;

class Construction
{
	public static function showBuildingQueue(User $user, Planet $planet)
	{
		$queueManager = new Queue($user, $planet);

		$queueItems = $queueManager->get($queueManager::TYPE_BUILDING);

		$items = [];

		if (count($queueItems)) {
			$end = 0;

			foreach ($queueItems as $item) {
				if (!$end) {
					$end = $item->time->timestamp;
				}

				$entity = EntityFactory::create(
					$item->object_id,
					$item->level - ($item->operation == $item::OPERATION_BUILD ? 1 : 0),
					$planet
				);

				$elementTime = $entity->getTime();

				if ($item->operation == $item::OPERATION_DESTROY) {
					$elementTime = ceil($elementTime / 2);
				}

				if ($item->time && $item->time_end->timestamp - $item->time->timestamp != $elementTime) {
					$item->update([
						'time_end' => $item->time->addSeconds($elementTime),
					]);
				}

				$end += $elementTime;

				$items[] = [
					'item' 	=> $item->object_id,
					'level' => $item->level,
					'mode' 	=> $item->operation == $item::OPERATION_DESTROY,
					'time' 	=> $end - time(),
					'end' 	=> $end
				];
			}
		}

		return $items;
	}

	public static function queueList(User $user, Planet $planet)
	{
		$queueItems = (new Queue($user, $planet))
			->get(Queue::TYPE_SHIPYARD);

		if (empty($queueItems)) {
			return [];
		}

		$data = [];

		$end = 0;

		foreach ($queueItems as $item) {
			if (!$end) {
				$end = $item->time->timestamp;
			}

			$entity = EntityFactory::create($item->object_id);

			$time = $entity->getTime();

			$end += $time * $item->level;

			$row = [
				'id'	=> (int) $item->object_id,
				'count'	=> (int) $item->level,
				'time'	=> $time,
				'end'	=> $end
			];

			$data[] = $row;
		}

		return $data;
	}
}
