<?php

use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Url as UrlProvider;
use Phalcon\Mvc\Application;
use Phalcon\DI\FactoryDefault;

define('APP_PATH', realpath('..') . '/');

define('DEBUG', false);

ini_set('log_errors', 'On');
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('error_log', APP_PATH.'/php_errors.log');

try
{
	if (DEBUG)
	{
		require APP_PATH.'/vendor/autoload.php';

		$profiler = new \Fabfuel\Prophiler\Profiler();
		$benchmark = $profiler->start('\App', [], 'Init');
	}

	$config = new \Phalcon\Config\Adapter\Ini(APP_PATH . "/app/config/config.ini");

	include (APP_PATH . "/app/config/loader.php");
	include (APP_PATH . "/app/config/services.php");

	if (DEBUG)
	{
		$di->setShared('profiler', $profiler);
		$pluginManager = new \Fabfuel\Prophiler\Plugin\Manager\Phalcon($profiler);
		$pluginManager->register();
	}

    $application = new Application($di);

	include (APP_PATH . "/app/config/bootstrap.php");

	if (isset($benchmark))
		$profiler->stop($benchmark);

	$handle = $application->handle();

	if (DEBUG && $application->router->getControllerName() != '' && $application->router->getControllerName() != 'game' && $application->router->getControllerName() != 'chat' && $application->router->getControllerName() != 'admin')
	{
		$toolbar = new \Fabfuel\Prophiler\Toolbar($profiler);
		$toolbar->addDataCollector(new \Fabfuel\Prophiler\DataCollector\Request());
	}

	if ($application->request->isAjax())
	{
		$application->response->setJsonContent(
		[
			'status' 	=> $application->game->getRequestStatus(),
			'message' 	=> $application->game->getRequestMessage(),
			'html' 		=> str_replace(Array("\t"), "", $handle->getContent()).(isset($toolbar) ? $toolbar->render() : ''),
			'data' 		=> $application->game->getRequestData()
		]);
		$application->response->setContentType('text/json', 'utf8');
		$application->response->send();
	}
	else
	{
   		echo $handle->getContent();
	}
}
catch(\Exception $e)
{
    echo "PhalconException: ", $e->getMessage();
	echo "<br>".$e->getFile();
	echo "<br>".$e->getLine();
}

?>