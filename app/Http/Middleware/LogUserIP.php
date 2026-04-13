<?php

namespace App\Http\Middleware;

use App\Helpers;
use App\Models\LogsIp;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogUserIP
{
	public function handle(Request $request, Closure $next): Response
	{
		$response = $next($request);

		$ip = $request->ip();

		if (($user = $request->user()) && $user->ip != $ip && $request->ip() != '127.0.0.1') {
			$user->ip = $ip;
			$user->save();

			LogsIp::create([
				'user_id' => $user->id,
				'ip' => Helpers::convertIp($ip),
			]);
		}

		return $response;
	}
}
