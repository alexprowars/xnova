<?php

namespace App\Http\Resources;

use App\Engine\EntityFactory;
use App\Engine\Enums\QueueType;
use App\Models\Planet;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class QueueResource extends JsonResource
{
	public function __construct(protected User $user, protected ?Planet $planet = null)
	{
		parent::__construct($user);
	}

	public function toArray($request)
	{
		$items = [];

		$queue = $this->user->queue;
		$queue->loadMissing('planet');

		$queueItems = $queue->where('type', QueueType::BUILDING);

		foreach ($queueItems as $item) {
			$items[] = [
				'item' 	=> $item->object_id,
				'type'	=> $item->type,
				'level' => $item->level,
				'mode' 	=> $item->operation,
				'date' 	=> $item->date_end->utc()->toAtomString(),
				'planet_id' => $item->planet_id,
				'planet_name' => $item->planet->name ?? '',
			];
		}

		$queueItems = $queue->where('type', QueueType::RESEARCH);

		foreach ($queueItems as $item) {
			$items[] = [
				'item' 	=> $item->object_id,
				'type'	=> $item->type,
				'level' => $item->level,
				'mode' 	=> $item->operation,
				'date' 	=> $item->date_end->utc()->toAtomString(),
				'planet_id' => $item->planet_id,
				'planet_name' => $item->planet->name ?? '',
			];
		}

		$queueItems = $queue->where('type', QueueType::SHIPYARD);
		$endDate = [];

		foreach ($queueItems as $item) {
			$entity = EntityFactory::get($item->object_id, planet: $item->planet);

			$time = $entity->getTime();

			$endDate[$item->planet_id] ??= $item->date;
			$endDate[$item->planet_id] = $endDate[$item->planet_id]->addSeconds($time * $item->level);

			if ($endDate[$item->planet_id]->isPast()) {
				continue;
			}

			$items[] = [
				'item'		=> (int) $item->object_id,
				'type'		=> $item->type,
				'count'		=> (int) $item->level,
				'mode' 		=> $item->operation,
				'date'		=> $endDate[$item->planet_id]->utc()->toAtomString(),
				'time'		=> $time,
				'planet_id' => $item->planet_id,
				'planet_name' => $item->planet->name ?? '',
			];
		}

		usort($items, fn(array $a, array $b) => $a['date'] > $b['date'] ? 1 : -1);

		return $items;
	}
}
