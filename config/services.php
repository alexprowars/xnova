<?php

return [
	'facebook' => [
		'client_id' => env ( 'FB_CLIENT_ID'),
		'client_secret' => env ( 'FB_CLIENT_SECRET'),
		'redirect' => env ( 'FB_REDIRECT')
	],
	'vkontakte' => [
		'client_id' => env('VK_CLIENT_ID'),
		'client_secret' => env('VK_CLIENT_SECRET'),
		'redirect' => env('VK_REDIRECT')
	],
	'google' => [
		'client_id' => env('GG_CLIENT_ID'),
		'client_secret' => env('GG_CLIENT_SECRET'),
		'redirect' => env('GG_REDIRECT')
	],
];
