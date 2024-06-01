<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Models\Observers\UserObserver;
use App\Models\User;
use App\Listeners\UserAuthenticated;

class EventServiceProvider extends ServiceProvider
{
	protected $listen = [
		\Illuminate\Auth\Events\Authenticated::class => [
			UserAuthenticated::class,
		],
		\SocialiteProviders\Manager\SocialiteWasCalled::class => [
			'SocialiteProviders\\VKontakte\\VKontakteExtendSocialite@handle',
		],
	];

	public function boot()
	{
		parent::boot();

		User::observe(UserObserver::class);
	}
}
