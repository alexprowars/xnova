<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToGame
{
	public function handle(Request $request, Closure $next): Response
	{
		if ($request->user() !== null) {
			return redirect()->route('overview');
		}

		return $next($request);
	}
}
