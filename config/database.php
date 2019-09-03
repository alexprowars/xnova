<?php

use Illuminate\Support\Str;

return [
	'default' => env('DB_CONNECTION', 'mysql'),
	'connections' => [
		'mysql' => [
			'driver' => 'mysql',
			'url' => env('DATABASE_URL'),
			'host' => env('DB_HOST', '127.0.0.1'),
			'port' => env('DB_PORT', '3306'),
			'database' => env('DB_DATABASE', 'forge'),
			'username' => env('DB_USERNAME', 'forge'),
			'password' => env('DB_PASSWORD', ''),
			'unix_socket' => env('DB_SOCKET', ''),
			'charset' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'prefix' => '',
			'prefix_indexes' => true,
			'strict' => false,
			'engine' => null,
			'options' => extension_loaded('pdo_mysql') ? array_filter([
				PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
			]) : [],
		],
	],
	'migrations' => 'migrations',
	'redis' => [
		'client' => env('REDIS_CLIENT', 'predis'),
		'options' => [
			'cluster' => env('REDIS_CLUSTER', 'predis'),
			'prefix' => Str::slug(env('APP_NAME', 'laravel'), '_').'_database_',
		],
		'default' => [
			'host' => env('REDIS_HOST', '127.0.0.1'),
			'password' => env('REDIS_PASSWORD', null),
			'port' => env('REDIS_PORT', 6379),
			'database' => env('REDIS_DB', 0),
		],
		'cache' => [
			'host' => env('REDIS_HOST', '127.0.0.1'),
			'password' => env('REDIS_PASSWORD', null),
			'port' => env('REDIS_PORT', 6379),
			'database' => env('REDIS_CACHE_DB', 1),
		],
	],
];
