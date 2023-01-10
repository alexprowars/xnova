<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
	public function boot()
	{
		/*DB::listen(function($query) {
			dump($query->sql);
			dump($query->time);
		});*/
	}

	public function register()
	{
	}
}
