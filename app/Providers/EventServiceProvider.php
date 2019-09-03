<?php

namespace Xnova\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Xnova\Models\Observers\PlanetObserver;
use Xnova\Models\Observers\UserObserver;
use Xnova\Planet;
use Xnova\User;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [];

    public function boot()
    {
        parent::boot();

		Planet::observe(PlanetObserver::class);
		User::observe(UserObserver::class);
    }
}
