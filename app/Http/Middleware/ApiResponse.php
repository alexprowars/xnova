<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

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

		if (!$response instanceof JsonResponse) {
			return new JsonResponse(null, $response->status());
		}

		return $response;
	}
}
