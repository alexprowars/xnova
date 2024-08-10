<?php

namespace App\Engine;

use App\Engine\Enums\QueueConstructionType;
use App\Engine\Enums\QueueType;
use App\Models\Planet;
use App\Models\User;

class Construction
{
	public static function showBuildingQueue(User $user, Planet $planet, QueueType $type = null)
	{
		$queueItems = (new QueueManager($user, $planet))
			->get($type);

		$items = [];
		$end   = null;

		foreach ($queueItems as $item) {
			$end ??= $item->time;

			$entity = null;

			if ($item->type == QueueType::BUILDING) {
				$entity = EntityFactory::get(
					$item->object_id,
					$item->level - ($item->operation == QueueConstructionType::BUILDING ? 1 : 0),
					$planet
				);

				$elementTime = $entity->getTime();

				if ($item->operation == QueueConstructionType::DESTROY) {
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
					'type'	=> $item->type,
					'level' => $item->level,
					'mode' 	=> $item->operation,
					'time' 	=> $end->utc()->toAtomString(),
					'planet_id' => $item->planet_id,
				];
			}

			if ($item->type == QueueType::SHIPYARD) {
				$entity = EntityFactory::get($item->object_id);

				$time = $entity->getTime();

				$end = $end->addSeconds($time * $item->level);

				$items[] = [
					'item'		=> (int) $item->object_id,
					'type'		=> $item->type,
					'count'		=> (int) $item->level,
					'mode' 		=> $item->operation,
					'time_one'	=> $time,
					'time'		=> $end->utc()->toAtomString(),
					'planet_id' => $item->planet_id,
				];
			}
		}

		return $items;
	}
}
