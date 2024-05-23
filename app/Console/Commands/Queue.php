<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Exceptions\Exception;
use App\Models;
use App\Planet;
use App\User;
use App\Vars;

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
				->where('time_end', '<=', time() + 10)
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

					$queueManager = new \App\Queue($item->user, $planet);
					$queueManager->update();
				} catch (Exception $e) {
					file_put_contents(ROOT_PATH . '/php_errors.log', "\n\n" . $e->getMessage() . "\n\n", FILE_APPEND);

					echo $e->getMessage();
				}
			}

			$totalRuns++;
			sleep(TIME_LIMIT / MAX_RUNS);
		}

		echo "all queue updated\n";
	}
}
