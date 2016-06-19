<?php

include_once(APP_PATH."app/functions.php");

if (isset($application))
{
	$application->registerModules([
		'admin' =>
		[
			'className'	=> 'Xnova\Admin\Module',
			'path'		=> APP_PATH.'app/modules/admin/Module.php',
		],
		'game' =>
		[
			'className'	=> 'App\Module',
			'path'		=> APP_PATH.'app/modules/game/Module.php',
		],
		'bot' =>
		[
			'className'	=> 'Xnova\Bot\Module',
			'path'		=> APP_PATH.'app/modules/bot/Module.php',
		]
	]);
}

//$result = $di->get('cache')->get('app_config');

//if (!is_array($result))
//{
	$result = [];

	$loads = $di->get('db')->query("SELECT `key`, `value` FROM game_config");

	while ($load = $loads->fetch())
		$result[$load['key']] = $load['value'];

//	$di->get('cache')->save('app_config', $result, 300);
//}

$di->get('config')->merge(new \Phalcon\Config(['app' => $result]));

if ($di->has('auth'))
{
	$di->get('auth')->addAuthPlugin('\App\Auth\Plugins\Ulogin');
	$di->get('auth')->addAuthPlugin('\App\Auth\Plugins\Vk');
	$di->get('auth')->addAuthPlugin('\App\Auth\Plugins\Ok');
	$di->get('auth')->checkExtAuth();
}

define('VERSION', '3.0.2');
define('DB_PREFIX', 'game_');