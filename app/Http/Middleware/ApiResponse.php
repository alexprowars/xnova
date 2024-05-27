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
			return new JsonResponse([
				'redirect' => $response->getTargetUrl(),
			]);
		}

		if ($response->exception) {
			if (!($response->exception instanceof Exception)) {
				$code = 500;

				if ($response->exception instanceof HttpException) {
					$code = $response->exception->getStatusCode();
				}

				return new JsonResponse($response->getOriginalContent(), $code);
			} else {
				return new JsonResponse(array_merge(Responce::make(null)->toArray(request()), $response->getOriginalContent()));
			}
		}

		if (!$response instanceof JsonResponse) {
			return new JsonResponse(null, $response->status());
		}

		if ($request->routeIs('state'))  {
			return $response;
		}

		if ($request->is('broadcasting/*')) {
			return $response;
		}

		return (new Responce($response->getOriginalContent()))->response();
	}
}
