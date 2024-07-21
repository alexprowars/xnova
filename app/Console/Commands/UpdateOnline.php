<?php

namespace App\Console\Commands;

use App\Settings;
use Illuminate\Console\Command;
use App\Models;

class UpdateOnline extends Command
{
	protected $signature = 'game:update.online';
	protected $description = '';

	public function handle()
	{
		$online = Models\User::query()
			->where('onlinetime', '>', now()->subSeconds(config('game.onlinetime') * 60))
			->count();

		$settings = app(Settings::class);
		$settings->usersOnline = $online;
		$settings->save();

		echo $online . " users online\n";
	}
}
