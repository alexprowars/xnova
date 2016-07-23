<?php

namespace Admin;

use Friday\Core\Module\Base;
use Phalcon\DiInterface;
use Phalcon\Mvc\ModuleDefinitionInterface;

class Module extends Base implements ModuleDefinitionInterface
{
	public function getModuleName()
	{
		return 'admin';
	}

	public function registerAutoloaders(DiInterface $di = null)
	{}

	public function registerServices(DiInterface $di)
	{
		$view = $di->getShared('view');

		$viewDirs = $view->getViewsDir();

		if (is_null($viewDirs))
			$viewDirs = [];

		if (!is_array($viewDirs))
			$viewDirs = [$viewDirs];

		$config = $di->getShared('config');

		$viewDirs[] = ROOT_PATH.$config->application->baseDir.$config->application->modulesDir.'Admin/Views';

		$view->setViewsDir($viewDirs);
	}
}