<?php

namespace App\Http\Middleware;

use App\Exceptions\PageException;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsVacationMode
{
	public function handle(Request $request, Closure $next): Response
	{
		$user = Auth::user();

		if ($user->isVacation()) {
			throw new PageException('Нет доступа! Включен режим отпуска');
		}

		return $next($request);
	}
}
