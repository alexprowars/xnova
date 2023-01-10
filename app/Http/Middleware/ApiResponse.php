<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Exceptions\Exception;
use App\Http\Resources\Responce;

class ApiResponse
{
	public function handle(Request $request, Closure $next): Response
	{
		/** @var JsonResponse $response */
		$response = $next($request);

		if ($response instanceof RedirectResponse) {
			$route = Route::current();

			$uri = rtrim(str_replace($request->root(), '', $response->getTargetUrl()), '/') . '/';
			$uri = str_replace('/' . $route->getPrefix(), '', $uri);

			return new JsonResponse([
				'data' => [
					'redirect' => $uri
				],
			]);
		}

		if (!($response instanceof JsonResponse)) {
			return $response;
		}

		if ($response->exception) {
			if (!($response->exception instanceof Exception)) {
				$code = 500;

				if ($response->exception instanceof HttpException) {
					$code = $response->exception->getStatusCode();
				}

				return new JsonResponse([
					'data' => $response->getOriginalContent(),
				], $code);
			} else {
				return new JsonResponse([
					'data' => array_merge(Responce::make(null)->toArray(request()), $response->getOriginalContent()),
				]);
			}
		}

		return (new Responce($response->getOriginalContent()))->response();
	}
}
