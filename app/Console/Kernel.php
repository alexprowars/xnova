<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console;

class Kernel extends Console\Kernel
{
	protected $commands = [
		Commands\UpdateOnline::class,
		Commands\UpdateStats::class,
		Commands\Fleet::class,
		Commands\Queue::class,
	];

	protected function schedule(Schedule $schedule)
	{
		$schedule->command(Commands\UpdateOnline::class)->everyFifteenMinutes();
		$schedule->command(Commands\UpdateStats::class)->cron('5 */6 * * *');

		$schedule->command(Commands\Fleet::class)->everyMinute()->runInBackground();
		$schedule->command(Commands\Queue::class)->everyMinute()->runInBackground();
	}
}
