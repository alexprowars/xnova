<?php

if (!extension_loaded('phalcon'))
	dl('phalcon.so');

if (!defined('ROOT_PATH'))
    define('ROOT_PATH', dirname(dirname(__FILE__)));

ini_set('apc.enable_cli', 1);

ini_set('log_errors', 'On');
ini_set('display_errors', 1);
error_reporting(E_ALL);

ini_set('error_log', ROOT_PATH.'/php_errors.log');

try
{
	require_once(ROOT_PATH."/app/modules/Core/Classes/Initializations.php");
	require_once(ROOT_PATH."/app/modules/Core/Classes/Cli.php");

	$application = new Friday\Core\Cli();
	$application->setTaskPath(ROOT_PATH.'/app/modules/Xnova/Tasks');
	$application->run();

	$application->getOutput();
}
catch (Exception $e)
{
    echo $e->getMessage()."\n";

	file_put_contents(ROOT_PATH.'/php_errors.log', "\n\n".print_r($_SERVER, true)."\n\n".print_r($_REQUEST, true)."\n\n".$e->getMessage()."\n\n", FILE_APPEND);

    exit(255);
}