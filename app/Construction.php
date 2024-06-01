<?php

namespace App;

use App\Engine\EntityFactory;
use App\Models\User;
use App\Models\Planet;

class Construction
{
	public static function showBuildingQueue(User $user, Planet $planet)
	{
		$queueManager = new Queue($user, $planet);

		$queueItems = $queueManager->get($queueManager::TYPE_BUILDING);

		$items = [];
		$end   = null;

		foreach ($queueItems as $item) {
			$end ??= $item->time;

			$entity = EntityFactory::get(
				$item->object_id,
				$item->level - ($item->operation == $item::OPERATION_BUILD ? 1 : 0),
				$planet
			);

			$elementTime = $entity->getTime();

			if ($item->operation == $item::OPERATION_DESTROY) {
				$elementTime = ceil($elementTime / 2);
			}

			if ($item->time && (int) $item->time->diffInSeconds($item->time_end) != $elementTime) {
				$item->update([
					'time_end' => $item->time->addSeconds($elementTime),
				]);
			}

			$end = $end->addSeconds($elementTime);

			$items[] = [
				'item' 	=> $item->object_id,
				'level' => $item->level,
				'mode' 	=> $item->operation == $item::OPERATION_DESTROY,
				'time' 	=> $end->utc()->toAtomString(),
			];
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
		$end  = null;

		foreach ($queueItems as $item) {
			$end ??= $item->time;

			$entity = EntityFactory::get($item->object_id);

			$time = $entity->getTime();

			$end = $end->addSeconds($time * $item->level);

			$row = [
				'id'		=> (int) $item->object_id,
				'count'		=> (int) $item->level,
				'time_one'	=> $time,
				'time'		=> $end->utc()->toAtomString(),
			];

			$data[] = $row;
		}

		return $data;
	}
}
