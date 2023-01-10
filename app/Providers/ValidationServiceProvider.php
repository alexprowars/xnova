<?php

/** @noinspection PhpUnusedParameterInspection */

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class ValidationServiceProvider extends ServiceProvider
{
	public function boot()
	{
		Validator::extend('galaxy', function ($attribute, $value) {
			$value = (int) $value;

			return !($value > config('settings.maxGalaxyInWorld') || $value < 1);
		});

		Validator::extend('system', function ($attribute, $value) {
			$value = (int) $value;

			return !($value > config('settings.maxSystemInGalaxy') || $value < 1);
		});

		Validator::extend('planet', function ($attribute, $value) {
			$value = (int) $value;

			return !($value > config('settings.maxPlanetInSystem') || $value < 1);
		});
	}
}
