<?php

namespace App\Providers;

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;

class AuthServiceProvider extends ServiceProvider
{
	protected $policies = [];

	public function boot(GateContract $gate)
	{
		$gate->before(function ($user) {
			return $user->id === 1;
		});

		Authenticate::redirectUsing(function () {
			return '/';
		});
	}
}
