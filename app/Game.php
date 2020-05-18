<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace Xnova;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Xnova\Models\Money;
use Xnova\Models\User;

class Game
{
	public static function datezone($format, $time = 0)
	{
		if ($time == 0) {
			$time = time();
		}

		if (Auth::check() && !is_null(Auth::user()->getUserOption('timezone'))) {
			$time += Auth::user()->getUserOption('timezone') * 1800;
		}

		return date($format, $time);
	}

	public static function getSpeed($type = '')
	{
		if ($type == 'fleet') {
			return (int) config('settings.fleet_speed', 2500) / 2500;
		}
		if ($type == 'mine') {
			return (int) config('settings.resource_multiplier', 1);
		}
		if ($type == 'build') {
			return round((int) config('settings.game_speed', 2500) / 2500, 1);
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

		$res = DB::selectOne("SELECT `id` FROM moneys where `ip` = '" . $ip . "' AND `time` > '" . (time() - 86400) . "'");

		if ($res) {
			return;
		}

		Money::query()->create([
			'user_id'		=> $user->id,
			'time'			=> time(),
			'ip'			=> $ip,
			'referer'		=> Request::server('HTTP_REFERER'),
			'user_agent'	=> Request::server('HTTP_USER_AGENT'),
		]);

		$user->links++;
		$user->refers++;
		$user->update();

		Session::put('ref', $user->id);
	}
}
