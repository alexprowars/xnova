<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToStart
{
	public function handle(Request $request, Closure $next): Response
	{
		if ($request->user() && (!$request->user()->race || !$request->user()->sex)) {
			return redirect()->route('start');
		}

		return $next($request);
	}
}
