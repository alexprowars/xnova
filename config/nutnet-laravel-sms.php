<?php

return [
	'provider' => env('NUTNET_SMS_PROVIDER', 'log'),
	'provider_options' => [
		'login' => env('NUTNET_SMS_LOGIN'),
		'password' => env('NUTNET_SMS_PASSWORD'),
	],
];
