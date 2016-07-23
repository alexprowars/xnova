<?php

namespace Friday\Core;

use Phalcon\DiInterface;
use Phalcon\Mvc\ModuleDefinitionInterface;

class Module implements ModuleDefinitionInterface
{
	public function registerAutoloaders(DiInterface $di = null) {}

	public function registerServices(DiInterface $di) {}
}