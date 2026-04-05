<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LocaleDetect
{
	public function handle(Request $request, Closure $next): mixed
	{
		if ($request->header('Locale')) {
			app()->setLocale($request->header('Locale'));
		}

		if ($request->user()) {
			app()->setLocale($request->user()->locale);
		}

		return $next($request);
	}
}
