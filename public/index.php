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

try
{
	$config = new \Phalcon\Config\Adapter\Ini(APP_PATH . '/app/config/config.ini');

	include (APP_PATH . '/app/config/loader.php');
	include (APP_PATH . '/app/config/services.php');

    $application = new Application($di);

	include (APP_PATH . '/app/config/bootstrap.php');

	$handle = $application->handle();

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