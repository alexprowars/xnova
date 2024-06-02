<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
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
}
