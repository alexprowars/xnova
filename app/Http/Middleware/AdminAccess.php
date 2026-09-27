<?php

namespace App\Http\Middleware;

use App\Admin\Access;
use Closure;
use Illuminate\Http\Request;

class AdminAccess
{
	public function handle(Request $request, Closure $next): mixed
	{
		Access::authorize('panel');

		return $next($request);
	}
}
