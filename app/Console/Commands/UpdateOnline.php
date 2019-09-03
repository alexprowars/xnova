<?php

namespace Xnova\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Xnova\Models;
use Xnova\Models\Options;

class UpdateOnline extends Command
{
    protected $signature = 'game:update.online';
    protected $description = '';

    public function handle ()
    {
		$online = Models\Users::query()
			->where('onlinetime', '>', time() - Config::get('game.onlinetime') * 60)
			->count();

		Options::set('users_online', $online);

		echo $online." users online\n";
    }
}
