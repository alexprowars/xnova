<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use React\EventLoop\Loop;

class Daemon extends Command
{
	protected $signature = 'game:daemon';

	public function handle(): void
	{
		$loop = Loop::get();

		$loop->addPeriodicTimer(2, function () {
			new Fleet()->handle();

			gc_collect_cycles();
		});

		$loop->addPeriodicTimer(2, function () {
			new Queue()->handle();

			gc_collect_cycles();
		});

		$loop->addPeriodicTimer(1800, function () use ($loop) {
			$loop->stop();
		});
	}
}
