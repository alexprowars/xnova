<?php

namespace Xnova\Admin;

use Phalcon\DiInterface;
use Phalcon\Loader;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Mvc\View;
use Phalcon\Events\Manager as EventsManager;

class Module implements ModuleDefinitionInterface
{
	public function registerAutoloaders(DiInterface $di = null)
	{
		$loader = new Loader();

		$config = $di->getShared('config');

		/** @noinspection PhpUndefinedFieldInspection */
		$loader->registerNamespaces(array
		(
		    'Xnova\Admin\Controllers' => APP_PATH.$config->application->baseDir.$config->application->modulesDir.'admin/'.$config->application->controllersDir,
		));

		$loader->register();
	}

	public function registerServices(DiInterface $di)
	{
		$config = $di->getShared('config');

		$di->setShared('view', function() use ($config)
		{
			$view = new View();
			$view->setViewsDir(APP_PATH.$config->application->baseDir.$config->application->modulesDir.'admin/'.$config->application->viewsDir);
			return $view;
		});
		
		$di->set('dispatcher', function () use ($di)
		{
			$eventsManager = new EventsManager;
			/** @noinspection PhpUnusedParameterInspection */
			$eventsManager->attach("dispatch:beforeException", function($event, $dispatcher, $exception)
			{
				/**
				 * @var \Phalcon\Mvc\Dispatcher $dispatcher
				 * @var \Phalcon\Mvc\Dispatcher\Exception $exception
				 */
				switch ($exception->getCode())
				{
					case Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
					case Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
						$dispatcher->forward([
							'controller' => 'error',
							'action'	 => 'notFound',
						]);
						return false;
				}
		
				return true;
			});
		
			$dispatcher = new Dispatcher();
			$dispatcher->setDefaultNamespace('Xnova\Admin\Controllers');
			$dispatcher->setEventsManager($eventsManager);
			return $dispatcher;
		});
	}
}

?>