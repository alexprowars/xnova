<?php

namespace App\Console\Commands;

use App\Engine\Ai\AiPlayer;
use App\Models\Ai;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class AiUpdate extends Command
{
	protected $signature = 'game:ai {--user= : Update only the bot for the specified player} {--dry-run : Show development goals without changing the game}';
	protected $description = 'Development, reconnaissance and fleet missions for game bots';

	public function handle(): void
	{
		$players = Ai::query()->where('active', true)
			->when($this->option('user'), fn($query) => $query->where('user_id', $this->option('user')))
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

				if ($player->active) {
					new AiPlayer($player)->run();
				}
			} catch (Throwable $exception) {
				Log::error('ai.turn_failed', ['ai_id' => $player->id, 'user_id' => $player->user_id, 'exception' => $exception]);

				$this->error('Bot ' . $player->id . ': ' . $exception->getMessage());
			} finally {
				$lock->release();
			}
		}
	}
}
