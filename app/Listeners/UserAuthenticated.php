<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Authenticated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use App\Exceptions\PageException;
use App\Game;
use App\Helpers;
use App\Models\Blocked;
use App\User;

class UserAuthenticated
{
	public function handle(Authenticated $event)
	{
		$route = Route::current()->getName();

		if ($route === 'banned') {
			return;
		}

		/** @var User $user */
		$user = $event->user;

		if ($user->banned > time()) {
			throw new PageException('Ваш аккаунт заблокирован. Срок окончания блокировки: ' . Game::datezone("d.m.Y H:i:s", $user->banned) . '<br>Для получения дополнительной информации зайдите <a href="' . URL::to('banned/') . '">сюда</a>');
		} elseif ($user->banned > 0 && $user->banned < time()) {
			$user->banned = 0;

			Blocked::query()->where('user_id', $user->id)->delete();
		}

		if ($user->onlinetime < (time() - 30)) {
			$user->onlinetime = time();
		}

		$ip = Helpers::convertIp(Request::ip());

		if ($user->ip != $ip) {
			$user->ip = $ip;

			DB::table('log_ips')->insert([
				'id'	=> $user->id,
				'time'	=> time(),
				'ip'	=> $ip
			]);
		}

		if ($user->isDirty()) {
			$user->update();
		}
	}
}
