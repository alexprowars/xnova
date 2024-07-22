<?php

namespace App\Providers;

use App\Mail\UserLostPassword;
use App\Models\User;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Contracts\Auth\Access\Gate;

class AuthServiceProvider extends ServiceProvider
{
	public function boot(Gate $gate)
	{
		$gate->before(function ($user) {
			return $user->id === 1;
		});

		Authenticate::redirectUsing(function () {
			return '/';
		});

		ResetPassword::toMailUsing(function (User $notifiable, $token) {
			return (new UserLostPassword($notifiable, $token))
				->to($notifiable->getEmailForPasswordReset());
		});
	}
}
