<?php

namespace App\Listeners;

use App\Engine\Game;
use App\Exceptions\Exception;
use App\Models\Blocked;
use App\Models\User;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

class UserAuthenticated
{
	public function handle(Authenticated $event): void
	{
		$route = Route::current()->getName();

		if ($route === 'banned') {
			return;
		}

		/** @var User $user */
		$user = $event->user;

		if ($user->blocked_at) {
			if ($user->blocked_at->isFuture()) {
				throw new Exception('Ваш аккаунт заблокирован. Срок окончания блокировки: ' . Game::datezone("d.m.Y H:i:s", $user->blocked_at->timezone($user->getOption('timezone'))) . '<br>Для получения дополнительной информации зайдите <a href="' . URL::to('/banned') . '">сюда</a>');
			}

			$user->blocked_at = null;

			Blocked::query()->whereBelongsTo($user)->delete();
		}

		if ($user->onlinetime->diffInSeconds() > 30) {
			$user->onlinetime = now();
		}

		$user->update();
	}
}
