<?php

use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\MainException;
use Xnova\Exceptions\PageException;
use Xnova\Exceptions\SuccessException;
use Xnova\Request;

if (!defined('ROOT_PATH'))
    define('ROOT_PATH', dirname(dirname(__FILE__)));

require_once(ROOT_PATH.'/vendor/autoload.php');

$_GET['_url'] = str_replace('/api', '', $_GET['_url'] ?? '');

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
	if ($e->getMessage() != '' && !($e instanceof PageException))
	{
		$type = 'notice';

		if ($e instanceof ErrorException)
			$type = 'error';
		elseif ($e instanceof SuccessException)
			$type = 'success';

		Request::addData('messages', [[
			'type' => $type,
			'text' => $e->getMessage(),
		]]);
	}

	if ($e instanceof PageException)
	{
		/** @var \Xnova\Controller $controller */
		$controller = $application->dispatcher->getActiveController();

		if ($controller)
			$controller->afterExecuteRoute();
	}

	$application->response->setJsonContent([
		'status' 	=> Request::getStatus(),
		'data' 		=> Request::getData()
	]);

	$application->response->setContentType('text/json', 'utf8');
	$application->response->send();
}
catch (Exception $e)
{
	echo $e->getMessage();

	file_put_contents(ROOT_PATH.'/php_errors.log',
		"\n\n".print_r($_SERVER, true)."
		\n\n".print_r($_REQUEST, true)."
		\n\n".$e->getMessage()."\n\n", FILE_APPEND);
}