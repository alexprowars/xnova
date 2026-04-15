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

	public function handle(): void
	{
		$planetsId = Models\Queue::query()
			->whereNotNull('date')
			->where('date_end', '<=', now()->addSeconds(5))
			->groupBy('planet_id')
			->pluck('planet_id');

		if ($planetsId->isEmpty()) {
			return;
		}

		$planets = Models\Planet::query()
			->with(['user'])
			->whereIn('id', $planetsId)
			->whereHas('user')
			->get();

		foreach ($planets as $planet) {
			try {
				DB::transaction(fn() => (new QueueManager($planet))->update());
			} catch (Exception $e) {
				Log::error("\n\n" . $e->getMessage() . "\n\n");

				$this->error($e->getMessage());
			}
		}
	}
}
