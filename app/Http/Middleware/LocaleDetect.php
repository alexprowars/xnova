<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LocaleDetect
{
	public function handle(Request $request, Closure $next): mixed
	{
		if ($request->header('locale')) {
			app()->setLocale($request->header('locale'));
		}

		if ($request->user()) {
			app()->setLocale($request->user()->locale);
		}

		return $next($request);
	}
}
