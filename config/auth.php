<?php

return [
	'defaults' => [
		'guard' => env('AUTH_GUARD', 'web'),
		'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
	],
	'guards' => [
		'api' => [
			'driver' => 'session',
			'provider' => 'users',
		],
	],
	'providers' => [
		'users' => [
			'driver' => 'eloquent',
			'model' => env('AUTH_MODEL', App\User::class),
		],
	],
	/*
	|--------------------------------------------------------------------------
	| Resetting Passwords
	|--------------------------------------------------------------------------
	|
	| You may specify multiple password reset configurations if you have more
	| than one user table or model in the application and you want to have
	| separate password reset settings based on the specific user types.
	|
	| The expire time is the number of minutes that the reset token should be
	| considered valid. This security feature keeps tokens short-lived so
	| they have less time to be guessed. You may change this as needed.
	|
	*/

	'passwords' => [
		'users' => [
			'provider' => 'users',
			'table' => 'password_resets',
			'expire' => 60,
			'throttle' => 60,
		],
	],
	'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),
];
