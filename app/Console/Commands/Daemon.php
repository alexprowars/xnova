<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Daemon extends Command
{
	protected $signature = 'game:daemon {worker : fleet or queue}';
	private bool $stopping = false;

	public function handle(): int
	{
		$commands = [
			'fleet' => Fleet::class,
			'queue' => Queue::class,
		];

		$worker = $this->argument('worker');

		if (!isset($commands[$worker])) {
			$this->error('Unknown worker: ' . $worker . '. Expected fleet or queue.');

			return self::INVALID;
		}

		$this->stopping = false;
		$this->trap([SIGINT, SIGTERM], function () {
			$this->stopping = true;
		});

		$startedAt = hrtime(true);
		$gcMemoryGrowth = max(1, (int) config('game.daemon.gc_memory_growth_mb', 32)) * 1024 * 1024;
		$memoryAfterGc = memory_get_usage();

		$this->info('Worker started: ' . $worker);

		while ((hrtime(true) - $startedAt) / 1_000_000_000 < 1800) {
			sleep(2);

			if ($this->stopping) {
				break;
			}

			$exitCode = $this->call($commands[$worker]);

			if ($exitCode !== self::SUCCESS) {
				return $exitCode;
			}

			if (memory_get_usage() - $memoryAfterGc >= $gcMemoryGrowth) {
				gc_collect_cycles();

				$memoryAfterGc = memory_get_usage();
			}
		}

		return self::SUCCESS;
	}
}
