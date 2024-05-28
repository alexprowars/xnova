<?php

namespace App\Providers;

use App\Http\Resources\StateResponce;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
	public function boot()
	{
		JsonResource::withoutWrapping();

		Response::macro('state', fn($value) => StateResponce::make($value));

		/*DB::listen(function($query) {
			dump($query->sql);
			dump($query->time);
		});*/
	}

	public function register()
	{
	}
}
