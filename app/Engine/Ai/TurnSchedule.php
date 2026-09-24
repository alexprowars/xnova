<?php

namespace App\Engine\Ai;

use App\Engine\Enums\QueueType;
use App\Engine\Game;
use App\Engine\QueueManager;
use App\Models\Planet;
use Carbon\CarbonImmutable;

class TurnSchedule
{
	public function __construct(private int $aiId)
	{
	}

	public function earliestRun(): CarbonImmutable
	{
		return now()->addSeconds(max(60, (int) config('ai.min_interval_seconds', 600)))->ceilMinute();
	}

	public function nextRun(string $activity): CarbonImmutable
	{
		$speed = $activity === 'fleet' ? Game::getSpeed('fleet') : Game::getSpeed('build');
		$seconds = (int) config('ai.' . $activity . '_interval_seconds', 600) / ($speed > 0 ? $speed : 1);
		$minutes = max(1, (int) ceil(max((int) config('ai.min_interval_seconds', 600), $seconds) / 60));
		$minute = intdiv($this->earliestRun()->timestamp, 60);
		// Постоянная фаза распределяет ботов по минутам, в том числе после перезапуска.
		$minute += ($this->aiId % $minutes - $minute % $minutes + $minutes) % $minutes;

		return now()->setTimestamp($minute * 60);
	}

	public function nextDevelopmentRun(Planet $planet, QueueManager $queue, ?float $resourceHours): CarbonImmutable
	{
		$next = null;
		$research = $queue->getResearch();
		$items = $queue->get()->reject(fn($item) => $item->type === QueueType::RESEARCH);

		if ($research) {
			$items->push($research);
		}

		$shipyardEnd = null;

		foreach ($items as $item) {
			if (!$item->date) {
				continue;
			}

			$end = $item->date_end;

			if ($item->type === QueueType::SHIPYARD) {
				// date_end верфи означает готовность одной единицы, а нужна вся очередь.
				$start = $shipyardEnd && $shipyardEnd->greaterThan($item->date) ? $shipyardEnd : $item->date;
				$shipyardEnd = $start->addSeconds($item->getTime() * $item->level);
				continue;
			}

			if ($end) {
				$next = $next?->min($end) ?? $end;
			}
		}

		if ($shipyardEnd) {
			$next = $next?->min($shipyardEnd) ?? $shipyardEnd;
		}

		if ($resourceHours !== null && is_finite($resourceHours)) {
			$ready = now()->addSeconds((int) ceil($resourceHours * 3600));

			$next = $next?->min($ready) ?? $ready;
		}

		// Долгое накопление или строительство не откладывает пересмотр остальных целей на недели.
		$review = now()->addSeconds((int) ceil(max(1 / 60, (float) config('ai.saving_horizon_hours', 3)) * 3600));

		return ($next ?? $this->nextRun('development'))->min($review)->max($this->earliestRun());
	}
}
