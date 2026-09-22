<?php

namespace App\Http\Middleware;

use App\Engine\Locale;
use Closure;
use Illuminate\Http\Request;

class LocaleDetect
{
	public function handle(Request $request, Closure $next): mixed
	{
		app()->setLocale(Locale::getPreferredLocale());

		return $next($request);
	}
}
