<?php

namespace Friday\Core\Module;

use Friday\Core\Modules;

abstract class Base
{
	public function __construct()
	{
		Modules::initialized($this->getModuleName());
	}

	abstract function getModuleName ();
}