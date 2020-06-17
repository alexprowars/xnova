<?php

namespace Xnova\Console\Commands;

use Illuminate\Console\Command;
use Xnova\Exceptions\Exception;
use Xnova\Models;
use Xnova\Planet;
use Xnova\User;
use Xnova\Vars;

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
				->where('time', '>', 0)
				->where('time_end', '<=', time() + 10)
				->where('type', '!=', 'unit')
				->orderBy('id', 'asc')
				->groupBy('user_id', 'planet_id')
				->get();

			foreach ($items as $item) {
				try {
					$user = User::query()->find((int) $item->user_id);

					$planet = Planet::query()->find((int) $item->planet_id);

					if ($planet) {
						$planet->setUser($user);
					}

					if (!$user || !$planet) {
						throw new Exception('Cron::update::queueAction::user or planet not found');
					}

					$queueManager = new \Xnova\Queue($user, $planet);
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
