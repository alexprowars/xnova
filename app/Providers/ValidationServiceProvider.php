<?php

/** @noinspection PhpUnusedParameterInspection */

namespace Xnova\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class ValidationServiceProvider extends ServiceProvider
{
	public function boot()
	{
		Validator::extend('galaxy', function ($attribute, $value) {
			$value = (int) $value;

			return !($value > Config::get('settings.maxGalaxyInWorld') || $value < 1);
		});

		Validator::extend('system', function ($attribute, $value) {
			$value = (int) $value;

			return !($value > Config::get('settings.maxSystemInGalaxy') || $value < 1);
		});

		Validator::extend('planet', function ($attribute, $value) {
			$value = (int) $value;

			return !($value > Config::get('settings.maxPlanetInSystem') || $value < 1);
		});
	}
}
