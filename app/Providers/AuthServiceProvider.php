<?php

namespace Xnova\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;

class AuthServiceProvider extends ServiceProvider
{
	protected $policies = [];

	public function boot(GateContract $gate)
	{
		$this->registerPolicies();

		Auth::provider('authuserprovider', function ($app, array $config) {
			return new AuthUserProvider($app['hash'], $config['model']);
		});

		$gate->before(function ($user) {
			return $user->id === 1;
		});
	}
}
