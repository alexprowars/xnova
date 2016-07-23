<?php

namespace Friday\Core;

use Phalcon\Cli\Console;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Di\FactoryDefault\Cli as CliDI;
use Phalcon\Loader;
use Phalcon\Registry;
use Phalcon\Config\Adapter\Ini as Config;

class Cli extends Console
{
	use Initializations;

	protected $_loaders =
	[
		'cache',
		'database'
	];
	private $_paths = [];

	private $arguments = [];
	
	public function __construct()
	{
		$di = new CliDI();

		$registry = new Registry();
		$this->_config = new Config(ROOT_PATH . '/app/config/core.ini');

		/** @noinspection PhpUndefinedFieldInspection */
		$registry->directories = (object)[
			'modules' => ROOT_PATH.$this->_config->application->baseDir.'modules/',
		];

		$di->set('registry', $registry);
		$di->setShared('config', $this->_config);

		parent::__construct();

		$this->setDI($di);
	}

	public function setTaskPath ($path)
	{
		if (!is_array($path))
			$path = [$path];

		$this->_paths = $path;
	}

	public function run ()
	{
		$di = $this->getDI();
		$di->setShared('app', $this);

		$eventsManager = new EventsManager();
		$this->setEventsManager($eventsManager);

		$namespaces = [];
		$namespaces['Friday\Core'] = ROOT_PATH.$this->_config->application->baseDir.'modules/Core/Classes';
		$namespaces['Friday\Core\Models'] = ROOT_PATH.$this->_config->application->baseDir.'modules/Core/Models';

		$loader = new Loader();

		$loader->registerNamespaces($namespaces);
		$loader->register();

		$di->set('loader', $loader);

		$loader = $this->initLoaders($di);

		foreach ($this->_loaders as $service)
		{
			$serviceName = ucfirst($service);

			$eventsManager->fire('init:before'.$serviceName, null);
			$result = $this->{'init'.$serviceName}($di, $eventsManager);
			$eventsManager->fire('init:after'.$serviceName, $result);
		}

		$di->set('eventsManager', $eventsManager, true);

		$loader->registerDirs($this->_paths);

		global $argv;

		foreach ($argv as $k => $arg)
		{
		    if ($k == 1)
		        $this->arguments['task'] = $arg;
			elseif ($k == 2)
				$this->arguments['action'] = $arg;
			elseif ($k >= 3)
				$this->arguments['params'][] = $arg;
		}

		define('CURRENT_TASK',   (isset($this->arguments['task']) ? $this->arguments['task'] : null));
		define('CURRENT_ACTION', (isset($this->arguments['action']) ? $this->arguments['action'] : null));
	}

	public function getOutput()
	{
		$this->handle($this->arguments);
	}
}