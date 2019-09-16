<?php

namespace Xnova\Providers;

use Illuminate\Foundation\Providers\ConsoleSupportServiceProvider;
use Illuminate\Queue\QueueServiceProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot ()
    {
		/*DB::listen(function($query) {
			dump($query->sql);
			dump($query->time);
		});*/
    }

	public function register()
	{
	    if ($this->app->environment() !== 'production')
	    {
			//$this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
		}

		if ($this->app->runningInConsole())
		{
			$this->app->register(ConsoleSupportServiceProvider::class);
			$this->app->register(QueueServiceProvider::class);
		}
	}
}