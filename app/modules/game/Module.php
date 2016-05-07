<?php

namespace App;

use Phalcon\DiInterface;
use Phalcon\Loader;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Mvc\View;

class Module implements ModuleDefinitionInterface
{
	public function registerAutoloaders(DiInterface $di = null)
	{
		$loader = new Loader();

		$config = $di->getShared('config');

		/** @noinspection PhpUndefinedFieldInspection */
		$loader->registerNamespaces(array
		(
		    'App\Controllers' 	=> APP_PATH.$config->application->baseDir.$config->application->modulesDir.'game/'.$config->application->controllersDir,
		));

		$loader->register();
	}

	public function registerServices(DiInterface $di)
	{
		$config = $di->getShared('config');

		$di->setShared('view', function() use ($config)
		{
			$view = new View();
			$view->setViewsDir(APP_PATH.$config->application->baseDir.$config->application->modulesDir.'game/'.$config->application->viewsDir);
			return $view;
		});
	}
}

?>