<?php

namespace App\Facades;

use App\Services\GalaxyService;
use Illuminate\Support\Facades\Facade;

/**
 * @see GalaxyService
 */
class Galaxy extends Facade
{
	protected static function getFacadeAccessor(): string
	{
		return GalaxyService::class;
	}
}
