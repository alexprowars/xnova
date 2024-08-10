<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
	public function boot()
	{
		JsonResource::withoutWrapping();

		/*DB::listen(function($query) {
			dump($query->sql);
			dump($query->time);
		});*/
	}

	public function register()
	{
	}
}
