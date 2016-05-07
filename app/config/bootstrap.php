<?php

include_once(APP_PATH."app/functions.php");

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
	]
]);

$result = $di->get('cache')->get('app_config');

if ($result === null)
{
	$result = [];

	$loads = $di->get('db')->query("SELECT `key`, `value` FROM game_config");

	while ($load = $loads->fetch())
		$result[$load['key']] = $load['value'];

	$di->get('cache')->save('app_config', $result, 3600);
}

$di->get('config')->merge(new \Phalcon\Config(['app' => $result]));

if ($di->has('auth'))
{
	$di->get('auth')->addAuthPlugin('\App\Auth\Plugins\Ulogin');
	$di->get('auth')->addAuthPlugin('\App\Auth\Plugins\Vk');
	$di->get('auth')->checkExtAuth();
}

define('VERSION', '3.0');
define('DB_PREFIX', 'game_');