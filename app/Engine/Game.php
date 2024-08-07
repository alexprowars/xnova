<?php

namespace App\Engine;

use App\Helpers;
use App\Models\Money;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

class Game
{
	public static function datezone($format, int|string|Carbon|null $time = null)
	{
		if ($time instanceof Carbon) {
			$time = $time->getTimestamp();
		}

		if (is_string($time)) {
			$time = strtotime($time);
		}

		if (empty($time)) {
			$time = time();
		}

		if (!empty(Auth::user()?->getOption('timezone'))) {
			$time += Auth::user()->getOption('timezone') * 3600;
		}

		return date($format, $time);
	}

	public static function getSpeed($type = '')
	{
		if ($type == 'fleet') {
			return (int) config('game.fleet_speed', 2500) / 2500;
		}
		if ($type == 'mine') {
			return (int) config('game.resource_multiplier', 1);
		}
		if ($type == 'build') {
			return round((int) config('game.game_speed', 2500) / 2500, 1);
		}

		return 1;
	}

	public static function checkReferLink()
	{
		if (Session::has('uid')) {
			return;
		}

		$id = (int) Request::server('QUERY_STRING', 0);

		if (!$id) {
			return;
		}

		$user = User::find($id);

		if (!$user) {
			return;
		}

		$ip = Helpers::convertIp(Request::ip());

		$res = Money::query()
			->where('ip', $ip)
			->where('time', '>', now()->subDay())
			->exists();

		if ($res) {
			return;
		}

		Money::create([
			'user_id' => $user->id,
			'time' => now(),
			'ip' => $ip,
			'referer' => Request::server('HTTP_REFERER'),
			'user_agent' => Request::server('HTTP_USER_AGENT'),
		]);

		$user->links++;
		$user->refers++;
		$user->update();

		Session::put('ref', $user->id);
	}
}
