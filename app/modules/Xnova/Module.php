<?php

namespace App;

use Friday\Core\Module\Base;
use Phalcon\DiInterface;
use Phalcon\Mvc\ModuleDefinitionInterface;

class Module extends Base implements ModuleDefinitionInterface
{
	public function getModuleName()
	{
		return 'xnova';
	}

	public function registerAutoloaders(DiInterface $di = null)
	{
		$namespaces[__NAMESPACE__.'\Models'] = __DIR__.'/Models';

		$loader = $di->get('loader');

		$loader->registerNamespaces($namespaces, true);
		$loader->register();
	}

	public function registerServices(DiInterface $di)
	{
		$view = $di->getShared('view');

		$viewDirs = $view->getViewsDir();

		if (is_null($viewDirs))
			$viewDirs = [];

		if (!is_array($viewDirs))
			$viewDirs = [$viewDirs];

		$config = $di->getShared('config');

		$viewDirs[] = ROOT_PATH.$config->application->baseDir.$config->application->modulesDir.'Xnova/Views';

		$view->setViewsDir($viewDirs);
	}
}