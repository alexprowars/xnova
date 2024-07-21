<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class ValidationServiceProvider extends ServiceProvider
{
	public function boot()
	{
		Validator::extend('galaxy', function ($attribute, $value) {
			$value = (int) $value;

			return !($value > config('game.maxGalaxyInWorld') || $value < 1);
		});

		Validator::extend('system', function ($attribute, $value) {
			$value = (int) $value;

			return !($value > config('game.maxSystemInGalaxy') || $value < 1);
		});

		Validator::extend('planet', function ($attribute, $value) {
			$value = (int) $value;

			return !($value > config('game.maxPlanetInSystem') || $value < 1);
		});
	}
}
