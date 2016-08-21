<?php

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

define('VERSION', '3.0.3');
define('DB_PREFIX', 'game_');