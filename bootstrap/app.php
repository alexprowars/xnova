<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\LocaleDetect;
use App\Http\Middleware\LogUserIP;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
	->withRouting(
		web: __DIR__ . '/../routes/web.php',
		commands: __DIR__ . '/../routes/console.php',
		channels: __DIR__ . '/../routes/channels.php',
		health: '/up',
	)
	->withMiddleware(function (Middleware $middleware) {
		$middleware->web(append: [
			LogUserIP::class,
			LocaleDetect::class,
			HandleInertiaRequests::class,
		]);
	})
	->withExceptions(function (Exceptions $exceptions) {
	})
	->create();
