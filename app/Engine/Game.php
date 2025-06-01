<?php

namespace App\Engine;

use App\Helpers;
use App\Models\Money;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

class Game
{
	public static function datezone($format, int|string|Carbon|null $time = null)
	{
		$date = null;

		if ($time instanceof Carbon) {
			$date = $time;
		}

		if (is_numeric($time)) {
			$date = Carbon::createFromTimestamp($time);
		}

		if (is_string($time)) {
			$date = Carbon::parse($time);
		}

		if (empty($time)) {
			$date = now();
		}

		$timezone = auth()->user()?->getOption('timezone');

		if (!empty($timezone)) {
			$date = $date->setTimezone($timezone);
		}

		return $date->format($format);
	}

	public static function getSpeed(?string $type = null): float
	{
		if ($type == 'fleet') {
			return (float) config('game.fleet_speed', 1);
		}
		if ($type == 'mine') {
			return (float) config('game.resource_multiplier', 1);
		}
		if ($type == 'build') {
			return (float) config('game.game_speed', 1);
		}

		return 1.0;
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

	public static function getMerchantExchangeRate(): array
	{
		return [
			'metal' => 1,
			'crystal' => 2,
			'deuterium' => 4,
		];
	}
}
