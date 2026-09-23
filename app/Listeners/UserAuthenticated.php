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

		if ($route === 'blocked') {
			return;
		}

		/** @var User $user */
		$user = $event->user;

		if ($user->blocked_at) {
			if ($user->blocked_at->isFuture()) {
				throw new Exception(__('main.auth_account_blocked', [
					'date' => Game::datezone('d.m.Y H:i:s', $user->blocked_at->timezone($user->getOption('timezone'))),
					'url' => URL::route('blocked'),
				]));
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
