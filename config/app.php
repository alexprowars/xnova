<?php

return [
    'name' => env('APP_NAME', ''),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', null),
    'asset_url' => env('ASSET_URL', null),
    'timezone' => 'Europe/Moscow',
    'locale' => 'ru',
    'fallback_locale' => 'ru',
    'faker_locale' => 'ru_RU',
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',
    'providers' => [
        Illuminate\Auth\AuthServiceProvider::class,
        //Illuminate\Broadcasting\BroadcastServiceProvider::class,
        //Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        //Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        //Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        //Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        //Illuminate\Mail\MailServiceProvider::class,
        //Illuminate\Notifications\NotificationServiceProvider::class,
        //Illuminate\Pagination\PaginationServiceProvider::class,
        //Illuminate\Pipeline\PipelineServiceProvider::class,
        //Illuminate\Queue\QueueServiceProvider::class,
        //Illuminate\Redis\RedisServiceProvider::class,
        //Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        //Illuminate\Validation\ValidationServiceProvider::class,
        //Illuminate\View\ViewServiceProvider::class,

        /*
         * Package Service Providers...
         */

		//Nutnet\LaravelSms\ServiceProvider::class,
        /*
         * Application Service Providers...
         */
        Xnova\Providers\AppServiceProvider::class,
        //App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
		Xnova\Providers\EventServiceProvider::class,
		Xnova\Providers\RouteServiceProvider::class,
		Spatie\Permission\PermissionServiceProvider::class,
    ],
    'aliases' => [],
];
