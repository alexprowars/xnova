<?php

namespace App\Console\Commands;

use App\Engine\UpdateStatistics;
use App\Engine\Vars;
use Illuminate\Console\Command;

class UpdateStats extends Command
{
	protected $signature = 'game:update.stats';
	protected $description = '';

	public function handle()
	{
		Vars::init();

		$start = microtime(true);

		$statUpdate = new UpdateStatistics();

		//$statUpdate->inactiveUsers();
		$statUpdate->deleteUsers();
		$statUpdate->clearOldStats();
		$statUpdate->update();
		$statUpdate->addToLog();
		$statUpdate->clearGame();
		$statUpdate->buildRecordsCache();

		$end = microtime(true);

		echo "stats updated in " . ($end - $start) . " sec\n";
	}
}
