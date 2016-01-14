<?php

include_once(APP_PATH."app/functions.php");

$loads = $di->get('db')->query("SELECT `key`, `value` FROM game_config");

$result = array();

while ($load = $loads->fetch())
{
	$result[$load['key']] = $load['value'];
}

$di->get('config')->merge(new \Phalcon\Config(array('app' => $result)));

if ($di->has('auth') && !$di->get('auth')->isAuthorized())
{
	$di->get('auth')->addAuthPlugin('\App\Auth\Plugins\Ulogin');
	$di->get('auth')->checkExtAuth();
}

define('VERSION', '3.0');
 
?>