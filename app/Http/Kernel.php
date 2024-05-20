<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate;

class Kernel extends HttpKernel
{
	protected $middleware = [
		\Illuminate\Http\Middleware\HandleCors::class,
		\App\Http\Middleware\PreventRequestsDuringMaintenance::class,
		\Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
		\App\Http\Middleware\TrimStrings::class,
		\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
	];

	protected $middlewareGroups = [
		'api' => [
			\App\Http\Middleware\EncryptCookies::class,
			Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
			Illuminate\Session\Middleware\StartSession::class,
			Illuminate\View\Middleware\ShareErrorsFromSession::class,
			//\App\Http\Middleware\VerifyCsrfToken::class,
			Illuminate\Routing\Middleware\SubstituteBindings::class,
			Middleware\ApiResponse::class,
		],
		'admin' => [
			\App\Http\Middleware\EncryptCookies::class,
			Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
			Illuminate\Session\Middleware\StartSession::class,
			Illuminate\View\Middleware\ShareErrorsFromSession::class,
			//\App\Http\Middleware\VerifyCsrfToken::class,
			Middleware\AdminCanAccess::class,
			Middleware\AdminViewData::class,
		],
		'web' => []
	];

	protected $middlewareAliases = [
		'auth' => \App\Http\Middleware\Authenticate::class,
		'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
		'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
		'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
		'can' => \Illuminate\Auth\Middleware\Authorize::class,
		'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
		'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
		'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
		'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
		'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
	];
}
