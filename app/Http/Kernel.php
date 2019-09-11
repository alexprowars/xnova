<?php

namespace Xnova\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate;

class Kernel extends HttpKernel
{
	protected $middleware = [
		Middleware\CheckOverload::class,
		//Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
		//Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
	];

	protected $middlewareGroups = [
		'api' => [
			Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
			Illuminate\Session\Middleware\StartSession::class,
			Illuminate\Session\Middleware\AuthenticateSession::class,
			//Illuminate\Routing\Middleware\SubstituteBindings::class,
			Middleware\ApiResponse::class,
		],
		'admin' => [
			Middleware\AdminCanAccess::class,
			Middleware\AdminViewData::class,
		],
	];

	protected $routeMiddleware = [
		'auth' => Illuminate\Auth\Middleware\Authenticate::class,
		//'auth.basic' => Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
		//'bindings' => Illuminate\Routing\Middleware\SubstituteBindings::class,
		//'cache.headers' => Illuminate\Http\Middleware\SetCacheHeaders::class,
		//'can' => Illuminate\Auth\Middleware\Authorize::class,
		//'signed' => Illuminate\Routing\Middleware\ValidateSignature::class,
		//'throttle' => Illuminate\Routing\Middleware\ThrottleRequests::class,
		//'verified' => Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
	];

	protected $middlewarePriority = [
		Illuminate\Session\Middleware\StartSession::class,
		//Illuminate\View\Middleware\ShareErrorsFromSession::class,
		Illuminate\Session\Middleware\AuthenticateSession::class,
		//Illuminate\Routing\Middleware\SubstituteBindings::class,
		Illuminate\Auth\Middleware\Authorize::class,
		Middleware\ApiResponse::class,
		Middleware\AdminCanAccess::class,
		Middleware\AdminViewData::class,
	];
}