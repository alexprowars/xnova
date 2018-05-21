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

	public function registerAutoloaders(DiInterface $di = null) {}

	public function registerServices(DiInterface $di) {}
}