<?php

use Illuminate\Support\Str;

return [
	'default' => env('CACHE_DRIVER', 'file'),
	'stores' => [

		'array' => [
			'driver' => 'array',
		],

		'file' => [
			'driver' => 'file',
			'path' => storage_path('framework/cache/data'),
		],

		'memcached' => [
			'driver' => 'memcached',
			'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
			'sasl' => [
				env('MEMCACHED_USERNAME'),
				env('MEMCACHED_PASSWORD'),
			],
			'options' => [
				// Memcached::OPT_CONNECT_TIMEOUT => 2000,
			],
			'servers' => [
				[
					'host' => env('MEMCACHED_HOST', '127.0.0.1'),
					'port' => env('MEMCACHED_PORT', 11211),
					'weight' => 100,
				],
			],
		],
		'redis' => [
			'driver' => 'redis',
			'connection' => 'cache',
		],
	],
	'prefix' => env('CACHE_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_cache'),
];
