<?php

namespace Xnova\Bot;

use Phalcon\DiInterface;
use Phalcon\Loader;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Mvc\View;
use Phalcon\Events\Manager as EventsManager;

$this->useImplicitView(false);

class Module implements ModuleDefinitionInterface
{
	public function registerAutoloaders(DiInterface $di = null)
	{
		$loader = new Loader();

		$config = $di->getShared('config');

		/** @noinspection PhpUndefinedFieldInspection */
		$loader->registerNamespaces(array
		(
		    'Xnova\Bot\Controllers' => APP_PATH.$config->application->baseDir.$config->application->modulesDir.'bot/'.$config->application->controllersDir,
			'Longman'				=> APP_PATH.$config->application->baseDir.$config->application->libraryDir,
		));

		$loader->register();
	}

	public function registerServices(DiInterface $di)
	{
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
							'controller' => 'index',
							'action'	 => 'notFound',
						]);
						return false;
				}

				return true;
			});

			$dispatcher = new Dispatcher();
			$dispatcher->setDefaultNamespace('Xnova\Bot\Controllers');
			$dispatcher->setEventsManager($eventsManager);
			return $dispatcher;
		});
	}
}

?>