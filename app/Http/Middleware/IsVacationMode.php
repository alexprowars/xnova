<?php

namespace App\Http\Middleware;

use App\Exceptions\PageException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsVacationMode
{
	public function handle(Request $request, Closure $next): Response
	{
		if ($request->user()->isVacation()) {
			throw new PageException('Нет доступа! Включен режим отпуска');
		}

		return $next($request);
	}
}
