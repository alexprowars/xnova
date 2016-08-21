<?php

namespace Friday\Core;

use Phalcon\Di;
use Phalcon\Text;

class Modules
{
	static private $_initModules = [];

	static public function init ($moduleName)
	{
		$moduleName = Text::lower($moduleName);

		if (in_array($moduleName, self::$_initModules))
			return true;

		$di = Di::getDefault();

		/**
		 * @var $app Application
		 */
		$app = $di->getShared('app');

		if ($app->hasModule($moduleName))
		{
			$module = $app->getModule($moduleName);

			if (file_exists($module['path']))
			{
				include_once($module['path']);

				/**
				 * @var $moduleClass \Phalcon\Mvc\ModuleDefinitionInterface
				 */
				$moduleClass = new $module['className']();

				$moduleClass->registerAutoloaders($di);
				$moduleClass->registerServices($di);

				self::$_initModules[] = $moduleName;

				return true;
			}
		}

		return false;
	}

	static public function initialized ($moduleName)
	{
		$moduleName = Text::lower($moduleName);

		if (!in_array($moduleName, self::$_initModules))
			self::$_initModules[] = $moduleName;

		return true;
	}
}