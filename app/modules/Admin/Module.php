<?php

namespace Admin;

use Friday\Core\Module\Base;
use Friday\Core\Modules;
use Phalcon\DiInterface;
use Phalcon\Mvc\ModuleDefinitionInterface;

class Module extends Base implements ModuleDefinitionInterface
{
	public function getModuleName()
	{
		return 'admin';
	}

	public function registerAutoloaders(DiInterface $di = null)
	{
		$namespaces[__NAMESPACE__.'\Forms'] = __DIR__.'/Forms';

		$loader = $di->get('loader');

		$loader->registerNamespaces($namespaces, true);
		$loader->register();
	}

	public function registerServices(DiInterface $di)
	{
		Modules::init('xnova');

		if ($di->has('view'))
		{
			$view = $di->getShared('view');
			$config = $di->getShared('config');

			$view->setViewsDir(ROOT_PATH.$config->application->baseDir.$config->application->modulesDir.'Admin/Views');
		}
	}
}