<?php

namespace App\Listeners;

use App\Engine\Game;
use App\Exceptions\PageException;
use App\Helpers;
use App\Models\Blocked;
use App\Models\LogsIp;
use App\Models\User;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

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

		if ($user->blocked_at) {
			if ($user->blocked_at->isFuture()) {
				throw new PageException('Ваш аккаунт заблокирован. Срок окончания блокировки: ' . Game::datezone("d.m.Y H:i:s", $user->blocked_at->timezone($user->getOption('timezone'))) . '<br>Для получения дополнительной информации зайдите <a href="' . URL::to('/banned') . '">сюда</a>');
			} else {
				$user->blocked_at = null;

				Blocked::query()->whereBelongsTo($user)->delete();
			}
		}

		if ($user->onlinetime->diffInSeconds() > 30) {
			$user->onlinetime = now();
		}

		$ip = Helpers::convertIp(Request::ip());

		if ($user->ip != $ip) {
			$user->ip = $ip;

			LogsIp::create([
				'user_id' => $user->id,
				'ip' => $ip,
			]);
		}

		$user->update();
	}
}
