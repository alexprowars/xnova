<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Listeners\UserAuthenticated;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Inertia\Ssr\SsrRenderFailed;

class EventServiceProvider extends ServiceProvider
{
	protected $listen = [
		\Illuminate\Auth\Events\Authenticated::class => [
			UserAuthenticated::class,
		],
		\SocialiteProviders\Manager\SocialiteWasCalled::class => [
			'SocialiteProviders\\VKID\\VKIDExtendSocialite@handle',
		],
	];

	public function boot()
	{
		Event::listen(SsrRenderFailed::class, function (SsrRenderFailed $event) {
			Log::warning('SSR failed', $event->toArray());
		});
	}
}
