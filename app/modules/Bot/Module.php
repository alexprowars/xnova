<?php

namespace Bot;

use Friday\Core\Module\Base;
use Phalcon\DiInterface;
use Phalcon\Mvc\ModuleDefinitionInterface;

$this->useImplicitView(false);

class Module extends Base implements ModuleDefinitionInterface
{
	public function getModuleName()
	{
		return 'bot';
	}

	public function registerAutoloaders(DiInterface $di = null)
	{
		$namespaces['Longman'] = __DIR__.'/Classes';

		$loader = $di->get('loader');

		$loader->registerNamespaces($namespaces, true);
		$loader->register();
	}

	public function registerServices(DiInterface $di) {}
}