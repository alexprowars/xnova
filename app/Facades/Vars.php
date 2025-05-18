<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \App\Engine\Vars
 */
class Vars extends Facade
{
	protected static function getFacadeAccessor(): string
	{
		return \App\Engine\Vars::class;
	}
}
