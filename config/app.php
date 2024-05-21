<?php

return [
	'name' => env('APP_NAME', ''),
	'env' => env('APP_ENV', 'production'),
	'debug' => env('APP_DEBUG', false),
	'url' => env('APP_URL', 'http://localhost'),
	'asset_url' => env('ASSET_URL', null),
	'timezone' => env('APP_TIMEZONE', 'UTC'),
	'locale' => env('APP_LOCALE', 'en'),
	'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
	'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),
	'cipher' => 'AES-256-CBC',
	'key' => env('APP_KEY'),
	'previous_keys' => [
		...array_filter(
			explode(',', env('APP_PREVIOUS_KEYS', ''))
		),
	],
	'maintenance' => [
		'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
		'store' => env('APP_MAINTENANCE_STORE', 'database'),
	],
];
