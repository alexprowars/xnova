<?php

namespace Friday\Core;

use Friday\Core\Models\Module;
use Phalcon\Cli\Console;
use Phalcon\DiInterface;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Di\FactoryDefault\Cli as CliDI;
use Phalcon\Loader;
use Phalcon\Registry;
use Phalcon\Config\Adapter\Ini as Config;
use Phalcon\Text;

include_once(ROOT_PATH."/app/functions.php");

define('VALUE_TRUE', 'Y');
define('VALUE_FALSE', 'N');

/**
 * Class Cli
 * @property \stdClass|\Phalcon\Config _config
 */
class Cli extends Console
{
	use Initializations;

	protected $_loaders =
	[
		'cache'
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
		$di->set('config', $this->_config, true);

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
		$namespaces[__NAMESPACE__] = dirname(__DIR__).'/Classes';
		$namespaces[__NAMESPACE__.'\Models'] = dirname(__DIR__).'/Models';

		$loader = new Loader();

		$loader->registerNamespaces($namespaces);
		$loader->register();

		$di->set('loader', $loader);

		if (file_exists(ROOT_PATH.$this->_config->application->baseDir.'globals.php'))
			include_once(ROOT_PATH.$this->_config->application->baseDir.'globals.php');

		$this->initDatabase($di, $eventsManager);

		$registry = $di->get('registry');

		/** @noinspection PhpUndefinedFieldInspection */
		$registry->modules = Module::find()->toArray();

		$this->initModules($di);
		$this->initLoaders($di);

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

	/**
	 * @param $di DiInterface
	 * @return \Phalcon\Application
	 */
	protected function initModules ($di)
	{
		$registry = $di->get('registry');

		$modules = [];

		if (!empty($registry->modules))
		{
			foreach ($registry->modules as $module)
			{
				if ($module['active'] != VALUE_TRUE)
					continue;

				$namespace = ($module['namespace'] != '' ? $module['namespace'] : ucfirst($module['code']));

				$modules[mb_strtolower($module['code'])] = [
					'className'	=> $namespace.'\Module',
					'path' 		=> $registry->directories->modules.ucfirst($module['code']).'/Module.php'
				];
			}
		}

		return parent::registerModules($modules);
	}

	public function getOutput()
	{
		$this->handle($this->arguments);
	}

	public function hasModule ($name)
	{
		return (isset($this->_modules[Text::lower($name)]));
	}
}