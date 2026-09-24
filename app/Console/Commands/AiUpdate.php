<?php

namespace App\Console\Commands;

use App\Engine\Ai\AiPlayer;
use App\Engine\Ai\Combat\ThreatAssessment;
use App\Engine\Ai\Runtime\RunContext;
use App\Engine\Ai\Runtime\TurnSchedule;
use App\Models\Ai;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class AiUpdate extends Command
{
	protected $signature = 'game:ai'
		. ' {--user= : Update the specified bot immediately}'
		. ' {--dry-run : Show development goals without changing the game}'
		. ' {--threats-only : Only react to incoming attacks}';
	protected $description = 'Development, reconnaissance and fleet missions for game bots';

	public function handle(): void
	{
		$threatsOnly = (bool) $this->option('threats-only');
		$force = (bool) $this->option('user');
		$context = new RunContext();
		$activity = $threatsOnly ? 'defense' : 'turn';
		$players = Ai::query()
			->where('active', true)
			->when($this->option('user'), fn($query) => $query->where('user_id', $this->option('user')))
			->when(
				$threatsOnly,
				fn(Builder $query) => $query->whereIn('user_id', ThreatAssessment::incoming()->select('target_user_id')),
			)
			->when(
				!$threatsOnly && !$force && !$this->option('dry-run'),
				fn(Builder $query) => $query->where(function (Builder $query) {
					$query->whereNull('next_run_at')->orWhere('next_run_at', '<=', now());
				}),
			)
			->orderBy('id')
			->lazyById(100);

		foreach ($players as $player) {
			if ($this->option('dry-run')) {
				$this->table(
					['Player', 'Planet', 'Type', 'Item', 'Quantity', 'Resources for 1 unit', 'Reason'],
					new AiPlayer($player)->preview(),
				);
				continue;
			}

			$lock = Cache::lock('ai:player:' . $player->id, 600);

			if (!$lock->get()) {
				continue;
			}

			$started = hrtime(true);

			try {
				$player->refresh();

				if (!$player->active
					|| (!$force
						&& ($player->state['errors'][$activity]['retry_at'] ?? 0) > now()->timestamp)) {
					continue;
				}

				if ($threatsOnly) {
					new AiPlayer($player, $context)->defend();
				} elseif (!$force && !$player->next_run_at) {
					$schedule = new TurnSchedule($player->id);
					$player->update(['next_run_at' => $schedule->nextRun('fleet')->min($schedule->nextRun('development'))]);
				} elseif ($force || $player->next_run_at->lessThanOrEqualTo(now())) {
					new AiPlayer($player, $context)->run($force);
				}

				$state = $player->state ?? [];

				if (isset($state['errors'][$activity])) {
					unset($state['errors'][$activity]);
					$player->update(['state' => $state]);
				}
			} catch (Throwable $exception) {
				Log::error('ai.turn_failed', [
					'ai_id' => $player->id,
					'user_id' => $player->user_id,
					'exception' => $exception,
				]);

				$this->error('Bot ' . $player->id . ': ' . $exception->getMessage());

				try {
					// Перечитываем только уже зафиксированную память: часть хода могла откатиться.
					$player->refresh();
					$state = $player->state ?? [];
					$failures = min(10, ($state['errors'][$activity]['count'] ?? 0) + 1);
					$delay = min(
						(int) config('ai.retry_max_seconds', 3600),
						(int) config('ai.retry_seconds', 60) * (2 ** ($failures - 1)),
					);
					$retry = now()->addSeconds($delay);
					$state['errors'][$activity] = ['count' => $failures, 'retry_at' => $retry->timestamp];
					$player->update(['state' => $state] + ($threatsOnly ? [] : ['next_run_at' => $retry]));
				} catch (Throwable $retryException) {
					Log::error('ai.retry_failed', ['ai_id' => $player->id, 'exception' => $retryException]);
				}
			} finally {
				if (config('ai.log_decisions')) {
					Log::info(
						'ai.turn',
						[
							'ai_id' => $player->id,
							'activity' => $activity,
							'duration_ms' => round((hrtime(true) - $started) / 1e6),
						] + $context->metrics($player->user_id),
					);
				}

				$context->forgetPlayer($player->user_id);
				$lock->release();
			}
		}
	}
}
