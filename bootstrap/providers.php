<?php

return [
	//Nutnet\LaravelSms\ServiceProvider::class,
	SocialiteProviders\Manager\ServiceProvider::class,
	App\Providers\AppServiceProvider::class,
	App\Providers\AuthServiceProvider::class,
	App\Providers\EventServiceProvider::class,
	App\Providers\ValidationServiceProvider::class,
	Spatie\Permission\PermissionServiceProvider::class,
];