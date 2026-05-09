<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\LocaleDetect;
use App\Http\Middleware\LogUserIP;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
	->withRouting(
		web: __DIR__ . '/../routes/web.php',
		commands: __DIR__ . '/../routes/console.php',
		channels: __DIR__ . '/../routes/channels.php',
		health: '/up',
	)
	->withMiddleware(function (Middleware $middleware) {
		$middleware->web(prepend: [
			ThrottleRequests::using('global'),
		]);

		$middleware->web(append: [
			LogUserIP::class,
			LocaleDetect::class,
			HandleInertiaRequests::class,
			AddLinkHeadersForPreloadedAssets::class,
		]);
	})
	->withExceptions(function (Exceptions $exceptions) {
		$exceptions->dontReportDuplicates();

		$exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e): bool {
			return $request->is('api/*') || $request->expectsJson();
		});
	})
	->create();
