<?php

namespace App\Http\Middleware;

use App\Helpers;
use App\Models\Money;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CheckReferral
{
	public function handle(Request $request, Closure $next): Response
	{
		if (Session::has('uid')) {
			return $next($request);
		}

		$id = (int) $request->server('QUERY_STRING', 0);

		if (!$id) {
			return $next($request);
		}

		$user = User::findOne($id);

		if (!$user) {
			return $next($request);
		}

		$ip = Helpers::convertIp($request->ip());

		$exist = Money::query()
			->where('ip', $ip)
			->where('date', '>', now()->subDay())
			->exists();

		if ($exist) {
			return $next($request);
		}

		Money::create([
			'user_id' => $user->id,
			'ip' => $ip,
			'referer' => $request->server('HTTP_REFERER', ''),
			'user_agent' => $request->server('HTTP_USER_AGENT', ''),
		]);

		$user->links++;
		$user->refers++;
		$user->update();

		session()->put('ref', $user->id);

		return $next($request);
	}
}
