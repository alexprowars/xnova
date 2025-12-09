<?php

namespace App\Http\Resources;

use App\Engine\EntityFactory;
use App\Engine\Enums\QueueConstructionType;
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
		$queue = $this->user->queue;
		$queue->loadMissing('planet');

		$items = $this->getBuildingQueue();

		$queueItems = $queue->where('type', QueueType::RESEARCH);

		foreach ($queueItems as $item) {
			$items[] = [
				'item' 	=> $item->object_id,
				'type'	=> $item->type,
				'level' => $item->level,
				'mode' 	=> $item->operation,
				'date' 	=> $item->date_end?->utc()->toAtomString(),
				'planet_id' => $item->planet_id,
				'planet_name' => $item->planet->name ?? '',
			];
		}

		$queueItems = $queue->where('type', QueueType::SHIPYARD);
		$endDate = [];

		foreach ($queueItems as $item) {
			$time = $item->getTime();

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

		usort($items, [$this, 'sortQueue']);

		return $items;
	}

	protected function getBuildingQueue()
	{
		$result = [];
		$endTime = [];

		$queue = $this->user->queue->where('type', QueueType::BUILDING);

		foreach ($queue as $item) {
			$endTime[$item->planet_id] ??= $item->date;
			$endTime[$item->planet_id] = $endTime[$item->planet_id]
				->addSeconds($item->getTime());

			$result[] = [
				'item' 	=> $item->object_id,
				'type'	=> $item->type,
				'level' => $item->level,
				'mode' 	=> $item->operation,
				'date' 	=> $endTime[$item->planet_id]->utc()->toAtomString(),
				'planet_id' => $item->planet_id,
				'planet_name' => $item->planet->name ?? '',
			];
		}

		return $result;
	}

	private function sortQueue($a, $b)
	{
		if ($a['date'] == $b['date']) {
			return 0;
		}

		if (empty($a['date'])) {
			return 1;
		}

		if (empty($b['date'])) {
			return -1;
		}

		return $a['date'] > $b['date'] ? 1 : -1;
	}
}
