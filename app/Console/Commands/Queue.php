<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Exceptions\Exception;
use App\Models;
use App\Vars;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Queue extends Command
{
	protected $signature = 'game:queue';
	protected $description = '';

	public function handle()
	{
		Vars::init();

		define('MAX_RUNS', 15);
		define('TIME_LIMIT', 60);

		if (function_exists('sys_getloadavg')) {
			$load = sys_getloadavg();

			if ($load[0] > 3) {
				die('Server too busy. Please try again later.');
			}
		}

		$totalRuns = 1;

		while ($totalRuns < MAX_RUNS) {
			$items = Models\Queue::query()
				->select(['user_id', 'planet_id'])
				->where('time', '>', 0)
				->where('time_end', '<=', now()->addSeconds(5))
				->whereNot('type', 'unit')
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

					DB::transaction(fn() => (new \App\Queue($item->user, $planet))->update());
				} catch (Exception $e) {
					Log::error("\n\n" . $e->getMessage() . "\n\n");
					echo $e->getMessage();
				}
			}

			$totalRuns++;
			sleep(TIME_LIMIT / MAX_RUNS);
		}

		echo "all queue updated\n";
	}
}
