<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \App\Engine\Galaxy
 */
class Galaxy extends Facade
{
	protected static function getFacadeAccessor(): string
	{
		return \App\Engine\Galaxy::class;
	}
}
