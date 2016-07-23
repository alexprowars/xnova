<?php

require_once("../app/bootstrap.php");

die();
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Url as UrlProvider;
use Phalcon\Mvc\Application;
use Phalcon\DI\FactoryDefault;

define('APP_PATH', dirname(__DIR__.'../') . '/');

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
		$profiler->addAggregator(new \Fabfuel\Prophiler\Aggregator\Database\QueryAggregator());
		$profiler->addAggregator(new \Fabfuel\Prophiler\Aggregator\Cache\CacheAggregator());

		$benchmark = $profiler->start('\App', [], 'Init');
	}

	$config = new \Phalcon\Config\Adapter\Ini(APP_PATH . '/app/config/config.ini');

	include (APP_PATH . '/app/config/loader.php');
	include (APP_PATH . '/app/config/services.php');

	if (DEBUG)
		$di->setShared('profiler', $profiler);

    $application = new Application($di);

	include (APP_PATH . '/app/config/bootstrap.php');

	if (isset($benchmark))
		$profiler->stop($benchmark);

	if (DEBUG)
	{
		$eventManager = new \Phalcon\Events\Manager();
		$application->setEventsManager($eventManager);

		/** @noinspection PhpUnusedParameterInspection */
		$eventManager->attach('application:afterStartModule', function ($event, $application)
		{
			$pluginManager = new \Fabfuel\Prophiler\Plugin\Manager\Phalcon($application->getDI()->getShared('profiler'));
			$pluginManager->register();
		});
	}

	$handle = $application->handle();

	if (DEBUG)
	{
		$controller = $application->router->getControllerName();

		if ($controller !== '' && $controller !== 'chat' && $controller !== 'admin')
		{
			$toolbar = new \Fabfuel\Prophiler\Toolbar($profiler);
			$toolbar->addDataCollector(new \Fabfuel\Prophiler\DataCollector\Request());
		}

		/** @noinspection PhpUndefinedFieldInspection */
		if (!$application->auth->isAuthorized() || !$application->user->isAdmin())
			unset($toolbar);
	}

	if ($application->request->isAjax() && $application->dispatcher->getModuleName() !== 'admin')
	{
		/** @noinspection PhpUndefinedFieldInspection */
		$application->response->setJsonContent(
		[
			'status' 	=> $application->game->getRequestStatus(),
			'message' 	=> $application->game->getRequestMessage(),
			'html' 		=> str_replace("\t", ' ', $handle->getContent()),
			'data' 		=> $application->game->getRequestData()
		]);
		$application->response->setContentType('text/json', 'utf8');
		$application->response->send();
	}
	else
	{
   		echo $handle->getContent();

		if (isset($toolbar))
			echo $toolbar->render();
	}
}
catch(\Exception $e)
{
    echo 'PhalconException: ', $e->getMessage();
	echo '<br>'.$e->getFile();
	echo '<br>'.$e->getLine();
	echo '<pre>';
	print_r($e->getTraceAsString());
	echo '</pre>';
}

?>