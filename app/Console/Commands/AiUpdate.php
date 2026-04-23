<?php

namespace App\Console\Commands;

use App\Engine\Ai\AiPlayer;
use App\Models\Ai;
use Illuminate\Console\Command;

class AiUpdate extends Command
{
	protected $signature = 'game:ai';

	public function handle(): void
	{
		$players = Ai::query()
			->where('active', true)
			->get();

		foreach ($players as $player) {
			$ai = new AiPlayer($player);
			$ai->run();
		}
	}
}
