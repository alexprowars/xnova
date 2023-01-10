<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;
use App\User;

class AdminCanAccess
{
	public function handle(Request $request, Closure $next): Response
	{
		Auth::onceUsingId(1);

		if (Auth::guest()) {
			throw UnauthorizedException::notLoggedIn();
		}

		$user = Auth::user();

		if (!$user->getRoleNames()->count()) {
			throw new UnauthorizedException('access dined');
		}

		$route = Route::current();

		if (!$user->can('controller ' . $route->getName())) {
			throw UnauthorizedException::forPermissions(['controller ' . $route->getName()]);
		}

		return $next($request);
	}
}
