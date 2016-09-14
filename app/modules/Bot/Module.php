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
		$namespaces['GuzzleHttp'] = __DIR__.'/Classes/GuzzleHttp';
		$namespaces['Psr'] = __DIR__.'/Classes/Psr';

		require __DIR__.'/Classes/GuzzleHttp/functions.php';
		require __DIR__.'/Classes/GuzzleHttp/Psr7/functions.php';
		require __DIR__.'/Classes/GuzzleHttp/Promise/functions.php';

		$loader = $di->get('loader');

		$loader->registerNamespaces($namespaces, true);
		$loader->register();
	}

	public function registerServices(DiInterface $di) {}
}