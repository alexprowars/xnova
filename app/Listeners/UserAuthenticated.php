<?php

namespace App\Listeners;

use App\Models\LogIp;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use App\Exceptions\PageException;
use App\Game;
use App\Helpers;
use App\Models\Blocked;
use App\Models\User;

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

		if ($user->banned_time?->isFuture()) {
			throw new PageException('Ваш аккаунт заблокирован. Срок окончания блокировки: ' . Game::datezone("d.m.Y H:i:s", $user->banned_time) . '<br>Для получения дополнительной информации зайдите <a href="' . URL::to('/banned') . '">сюда</a>');
		} elseif ($user->banned_time?->isPast()) {
			$user->banned_time = null;

			Blocked::query()->where('user_id', $user->id)->delete();
		}

		if ($user->onlinetime->diffInSeconds() > 30) {
			$user->onlinetime = now();
		}

		$ip = Helpers::convertIp(Request::ip());

		if ($user->ip != $ip) {
			$user->ip = $ip;

			LogIp::create([
				'user_id' => $user->id,
				'ip' => $ip,
			]);
		}

		$user->update();
	}
}
