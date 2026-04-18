<?php

namespace App\Http\Middleware;

use App\Exceptions\Exception;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsVacationMode
{
	public function handle(Request $request, Closure $next): Response
	{
		if ($request->user()->isVacation()) {
			throw new Exception('Нет доступа! Включен режим отпуска');
		}

		return $next($request);
	}
}
