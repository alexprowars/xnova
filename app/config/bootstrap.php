<?php

include_once(APP_PATH."app/functions.php");

$di->get('auth')->checkExtAuth();

$loads = $di->get('db')->query("SELECT `key`, `value` FROM game_config");

$result = array();

while ($load = $loads->fetch())
{
	$result[$load['key']] = $load['value'];
}

$di->get('config')->merge(new \Phalcon\Config(array('app' => $result)));
 
?>