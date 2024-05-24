<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Models\Observers\PlanetObserver;
use App\Models\Observers\UserObserver;
use App\Models\Planet;
use App\Models\User;

class EventServiceProvider extends ServiceProvider
{
	protected $listen = [
		\Illuminate\Auth\Events\Authenticated::class => [
			'App\Listeners\UserAuthenticated',
		],
		\SocialiteProviders\Manager\SocialiteWasCalled::class => [
			'SocialiteProviders\\VKontakte\\VKontakteExtendSocialite@handle',
		],
	];

	public function boot()
	{
		parent::boot();

		Planet::observe(PlanetObserver::class);
		User::observe(UserObserver::class);
	}
}
