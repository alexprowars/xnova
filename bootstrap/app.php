<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

if (!defined('VERSION')) {
	define('VERSION', '5.0');
}

return Application::configure(basePath: dirname(__DIR__))
	->withRouting(
		web: __DIR__ . '/../routes/web.php',
		api: __DIR__ . '/../routes/api.php',
		commands: __DIR__ . '/../routes/console.php',
		channels: __DIR__ . '/../routes/channels.php',
		health: '/up',
	)
	->withMiddleware(function (Middleware $middleware) {
		$middleware->appendToGroup('api', [
			Illuminate\Cookie\Middleware\EncryptCookies::class,
			Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
			Illuminate\Session\Middleware\StartSession::class,
			Illuminate\View\Middleware\ShareErrorsFromSession::class,
			//Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
			\App\Http\Middleware\ApiResponse::class,
		]);
	})
	->withExceptions(function (Exceptions $exceptions) {
		$exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
			if ($request->is('api/*')) {
				return true;
			}

			return $request->expectsJson();
		});
	})->create();
