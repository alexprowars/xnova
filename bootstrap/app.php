<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

if (!defined('VERSION')) {
	define('VERSION', '5.0');
}

if (!function_exists('log_var')) {
	function log_var($name, $value)
	{
		if (is_array($value)) {
			$value = var_export($value);
		}

		log_comment("$name = $value");
	}
}

if (!function_exists('log_comment')) {
	function log_comment($comment)
	{
		echo "[log]$comment<br>\n";
	}
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

		$middleware->group('admin', [
			Illuminate\Cookie\Middleware\EncryptCookies::class,
			Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
			Illuminate\Session\Middleware\StartSession::class,
			Illuminate\View\Middleware\ShareErrorsFromSession::class,
			//Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
			\App\Http\Middleware\AdminCanAccess::class,
		]);
	})
	->withExceptions(function (Exceptions $exceptions) {
		$exceptions->render(function (Exception $e, Request $request) {
			$data = [
				'error' => [
					'message' => $e->getMessage(),
				]
			];

			if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
				return new JsonResponse($data, $e->getStatusCode(), options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
			}

			return new JsonResponse($data, 500, options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		});
		/*$exceptions->render(function (Exception $e, Request $request) {
			$data = [
				'message' => $e->getMessage(),
			];

			if ($e instanceof AuthenticationException) {
				$data['redirect'] = $e->redirectTo($request);
			} else {
				if (config('app.debug')) {
					$data['trace'] = $e->getTraceAsString();
				}
			}

			return new JsonResponse($data, options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		});*/
	})->create();
