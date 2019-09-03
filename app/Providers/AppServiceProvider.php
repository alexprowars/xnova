<?php

namespace Xnova\Providers;

use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Illuminate\Foundation\Providers\ConsoleSupportServiceProvider;
use Illuminate\Queue\QueueServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Xnova\Models\Options;

class AppServiceProvider extends ServiceProvider
{
    public function boot ()
    {
		if ($this->app->runningInConsole() === false && file_exists(base_path('.env')) && filesize(base_path('.env')) > 0)
		{
			$options = Options::getAll();

			foreach ($options as $name => $value)
				Config::set('game.'.$name, $value);
		}

		/*DB::listen(function($query) {
			dump($query->sql);
			dump($query->time);
		});*/
    }

	public function register()
	{
	    if ($this->app->environment() !== 'production')
	    {
			if ($this->app->runningInConsole())
				$this->app->register(IdeHelperServiceProvider::class);

			$this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
		}

		if ($this->app->runningInConsole())
		{
			$this->app->register(ConsoleSupportServiceProvider::class);
			$this->app->register(QueueServiceProvider::class);
		}
	}
}