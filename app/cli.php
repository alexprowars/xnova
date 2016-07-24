<?php

if (!extension_loaded('phalcon'))
	dl('phalcon.so');

if (!defined('ROOT_PATH'))
    define('ROOT_PATH', dirname(dirname(__FILE__)));

try
{
	require_once(ROOT_PATH."/app/modules/Core/Classes/Initializations.php");
	require_once(ROOT_PATH."/app/modules/Core/Classes/Cli.php");

	$application = new Friday\Core\Cli();
	$application->setTaskPath(ROOT_PATH.'/app/modules/Xnova/Tasks');
	$application->run();

	$application->getOutput();
}
catch (\Phalcon\Exception $e)
{
    echo $e->getMessage()."\n";
    exit(255);
}