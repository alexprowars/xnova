<?php

use Xnova\Exceptions\MainException;
use Xnova\Request;

if (!defined('ROOT_PATH'))
    define('ROOT_PATH', dirname(dirname(__FILE__)));

require_once(ROOT_PATH.'/vendor/autoload.php');

try
{
	require_once(ROOT_PATH."/app/modules/Core/Classes/Initializations.php");
	require_once(ROOT_PATH."/app/modules/Core/Classes/Application.php");

	$application = new Friday\Core\Application();
	$application->run();

	echo $application->getOutput();
}
catch (MainException $e)
{
	/** @var \Xnova\Controller $controller */
	$controller = $application->dispatcher->getActiveController();

	if ($controller)
		$controller->afterExecuteRoute();

	if ($application->request->isAjax())
	{
		Request::addData('html', str_replace(["\t", "\n"], '', $application->view->getContent()));

		$application->response->setJsonContent([
			'status' 	=> Request::getStatus(),
			'data' 		=> Request::getData()
		]);

		$application->response->setContentType('text/json', 'utf8');
		$application->response->send();

		return '';
	}
	else
	{
		$application->view->start();
		$application->view->render('error', 'index');
		$application->view->finish();

   		echo $application->view->getContent();
	}
}
catch (Exception $e)
{
	if (isset($application))
	{
		$di = $application->getDI();

		if ($di->has('view'))
		{
			$config = $di->getShared('config');
			$assets = $di->getShared('assets');
			$views  = $di->getShared('view');

			$assets->addCss('https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all');
			$assets->addCss('assets/admin/global/plugins/bootstrap/css/bootstrap.css');
			$assets->addCss('assets/admin/global/css/components.css');
			$assets->addCss('assets/admin/pages/css/error.css');

			$views->setViewsDir(ROOT_PATH.$config->application->baseDir.$config->application->modulesDir.'Admin/Views');
			$views->partial('fatal');

			$di->getShared('response')->setStatusCode(500, 'Fatal Error')->send();
		}
	}

	echo '<pre style="margin:25px;padding:25px">';
    echo 'Exception: ', $e->getMessage();
	echo '<br>'.$e->getFile();
	echo '<br>'.$e->getLine();
	echo '<pre style="margin:10px 0">';

	if (defined('SUPERUSER'))
		print_r($e->getTraceAsString());

	file_put_contents(ROOT_PATH.'/php_errors.log', "\n\n".print_r($_SERVER, true)."\n\n".print_r($_REQUEST, true)."\n\n".$e->getMessage()."\n\n", FILE_APPEND);

	echo '</pre>';
}