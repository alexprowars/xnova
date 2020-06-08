<?php

namespace Xnova\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Xnova\Exceptions\Exception;
use Xnova\Game;
use Xnova\Controller;
use Xnova\Http\Resources\Responce;
use Xnova\Models\UserQuest;
use Xnova\Planet\ApiData;
use Xnova\Vars;

class ApiResponse
{
	public function handle(Request $request, Closure $next): Response
	{
		Auth::onceUsingId(1);

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
			}
		}

		return (new Responce($response->getOriginalContent()))->response();
	}
}
