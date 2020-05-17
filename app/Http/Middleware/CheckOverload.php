<?php

namespace Xnova\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOverload
{
	public function handle(Request $request, Closure $next): Response
	{
		if (function_exists('sys_getloadavg')) {
			$load = sys_getloadavg();

			if ($load[0] > 15) {
				header('HTTP/1.1 503 Too busy, try again later');
				die('Server too busy. Please try again later.');
			}
		}

		return $next($request);
	}
}
