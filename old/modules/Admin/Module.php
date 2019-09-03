<?php

namespace Admin;

use Friday\Core\Module\Base;
use Friday\Core\Modules;
use Phalcon\DiInterface;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Mvc\View\Engine\Volt;

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
		$app = $di->getShared('app');

		$app->getEventsManager()->attach('view:afterEngineRegister', function ($event, Volt $volt)
		{
			$compiler = $volt->getCompiler();

			$compiler->addFunction('_text', function($arguments)
			{
				return '\Friday\Core\Lang::getText(' . $arguments . ')';
			});

			$compiler->addFilter('floor', 'floor');
			$compiler->addFilter('round', 'round');
			$compiler->addFilter('ceil', 'ceil');
			$compiler->addFunction('in_array', 'in_array');
			$compiler->addFunction('toJson', 'json_encode');
			$compiler->addFunction('replace', 'str_replace');
			$compiler->addFunction('preg_replace', 'preg_replace');
			$compiler->addFunction('md5', 'md5');
			$compiler->addFunction('min', 'min');
			$compiler->addFunction('max', 'max');
			$compiler->addFunction('floor', 'floor');
			$compiler->addFunction('ceil', 'ceil');
			$compiler->addFunction('is_email', 'is_email');
			$compiler->addFunction('htmlspecialchars', 'htmlspecialchars');
			$compiler->addFunction('rand', 'mt_rand');
			$compiler->addFunction('implode', 'implode');
			$compiler->addFunction('slashes', 'addslashes');
			$compiler->addFunction('array_search', 'array_search');
			$compiler->addFunction('number_format', 'number_format');
			$compiler->addFunction('pretty_number', function($arguments)
			{
				return '\Xnova\Format::number(' . $arguments . ')';
			});
			$compiler->addFunction('pretty_time', function($arguments)
			{
				return '\Xnova\Format::time(' . $arguments . ')';
			});
			$compiler->addFunction('option', function($arguments)
			{
				return '\Friday\Core\Options::get(' . $arguments . ')';
			});
			$compiler->addFunction('planetLink', function($arguments)
			{
				return '\Xnova\Helpers::BuildPlanetAdressLink(' . $arguments . ')';
			});
			$compiler->addFunction('morph', function($arguments)
			{
				return '\Xnova\Helpers::morph(' . $arguments . ')';
			});
		});

		Modules::init('xnova');

		if ($di->has('view'))
		{
			$view = $di->getShared('view');
			$config = $di->getShared('config');

			$view->setViewsDir(ROOT_PATH.$config->application->baseDir.$config->application->modulesDir.'Admin/Views');
		}
	}
}