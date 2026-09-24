<?php

namespace App\Console\Commands;

use App\Engine\Ai\AiPlayer;
use App\Engine\Ai\FleetCommander;
use App\Engine\Ai\RunContext;
use App\Engine\Ai\TurnSchedule;
use App\Models\Ai;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class AiUpdate extends Command
{
	protected $signature = 'game:ai {--user= : Update the specified bot immediately} {--dry-run : Show development goals without changing the game} {--threats-only : Only react to incoming attacks}';
	protected $description = 'Development, reconnaissance and fleet missions for game bots';

	public function handle(): void
	{
		$threatsOnly = (bool) $this->option('threats-only');
		$force = (bool) $this->option('user');
		$context = new RunContext();
		$players = Ai::query()->where('active', true)
			->when($this->option('user'), fn($query) => $query->where('user_id', $this->option('user')))
			->when($threatsOnly, fn(Builder $query) => $query->whereIn('user_id', FleetCommander::incomingThreats()->select('target_user_id')))
			->when(!$threatsOnly && !$force && !$this->option('dry-run'), fn(Builder $query) => $query->where(function (Builder $query) {
				$query->whereNull('next_run_at')->orWhere('next_run_at', '<=', now());
			}))
			->orderBy('id')->lazyById(100);

		foreach ($players as $player) {
			if ($this->option('dry-run')) {
				$this->table(['Player', 'Planet', 'Type', 'Item', 'Quantity', 'Resources for 1 unit', 'Reason'], new AiPlayer($player)->preview());
				continue;
			}

			$lock = Cache::lock('ai:player:' . $player->id, 600);

			if (!$lock->get()) {
				continue;
			}

			try {
				$player->refresh();

				if (!$player->active) {
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
			} catch (Throwable $exception) {
				Log::error('ai.turn_failed', ['ai_id' => $player->id, 'user_id' => $player->user_id, 'exception' => $exception]);

				$this->error('Bot ' . $player->id . ': ' . $exception->getMessage());
			} finally {
				$context->forgetPlayer($player->user_id);
				$lock->release();
			}
		}
	}
}
