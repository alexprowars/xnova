<?php

namespace Xnova\Console\Commands;

use Backpack\Settings\app\Models\Setting;
use Illuminate\Console\Command;
use Xnova\Models;

class UpdateOnline extends Command
{
	protected $signature = 'game:update.online';
	protected $description = '';

	public function handle()
	{
		$online = Models\User::query()
			->where('onlinetime', '>', time() - config('settings.onlinetime') * 60)
			->count();

		Setting::set('users_online', $online);

		echo $online . " users online\n";
	}
}
