<?php

namespace Xnova\Listeners;

use Illuminate\Auth\Events\Authenticated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Xnova\Exceptions\PageException;
use Xnova\Game;
use Xnova\Helpers;
use Xnova\Models\Blocked;
use Xnova\User;

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

			Blocked::query()->where('who', $user->id)->delete();
		}

		if ($user->onlinetime < (time() - 30)) {
			$user->onlinetime = time();
		}

		$ip = Helpers::convertIp(Request::ip());

		if ($user->ip != $ip) {
			$user->ip = $ip;

			DB::table('log_ip')->insert([
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
