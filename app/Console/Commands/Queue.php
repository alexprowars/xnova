<?php

namespace App\Console\Commands;

use App\Engine\QueueManager;
use App\Exceptions\Exception;
use App\Models;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Queue extends Command
{
	protected $signature = 'game:queue';

	public function handle()
	{
		$items = Models\Queue::query()
			->select(['user_id', 'planet_id'])
			->where('date', '>', 0)
			->where('date_end', '<=', now()->addSeconds(5))
			//->orderBy('id')
			->groupBy('user_id', 'planet_id')
			->with(['user', 'planet'])
			->get();

		foreach ($items as $item) {
			try {
				$planet = $item->planet;
				$planet?->setRelation('user', $item->user);

				if (!$item->user || !$planet) {
					throw new Exception('Cron::update::queueAction::user or planet not found');
				}

				DB::transaction(fn() => (new QueueManager($planet))->update());
			} catch (Exception $e) {
				Log::error("\n\n" . $e->getMessage() . "\n\n");

				$this->error($e->getMessage());
			}
		}
	}
}
