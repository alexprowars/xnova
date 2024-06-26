<?php

namespace App\Console\Commands;

use Backpack\Settings\app\Models\Setting;
use Illuminate\Console\Command;
use App\Models;

class UpdateOnline extends Command
{
	protected $signature = 'game:update.online';
	protected $description = '';

	public function handle()
	{
		$online = Models\User::query()
			->where('onlinetime', '>', now()->subSeconds(config('settings.onlinetime') * 60))
			->count();

		Setting::set('usersOnline', $online);

		echo $online . " users online\n";
	}
}
