<?php

namespace Friday\Core;

use Friday\Core\Models\Module;
use Phalcon\Config\Adapter\Ini as Config;
use Phalcon\DI;
use Phalcon\DiInterface;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Loader;
use Phalcon\Mvc\Application as PhalconApplication;
use Phalcon\Registry;
use Fabfuel\Prophiler;
use Friday\Core\Helpers\Cache as CacheHelper;
use Phalcon\Text;
use Phalcon\Version;

include_once(ROOT_PATH."/app/functions.php");

define('VALUE_TRUE', 'Y');
define('VALUE_FALSE', 'N');

/**
 * Class Application
 * @property \stdClass|\Phalcon\Config _config
 */
class Application extends PhalconApplication
{
	use Initializations;

	protected $_loaders =
	[
		'cache',
		'environment',
		'routers',
		'flash',
		'session',
		'view',
		'dispatcher'
	];

	public function __construct()
	{
		if (!class_exists('\Phalcon\Version'))
			throw new \Exception('Phalcon extensions not loaded');

		$version = Version::getPart(Version::VERSION_MAJOR);

		if ($version < 3)
			throw new \Exception('Required Phalcon 3.0.0 and above');

		define('INSTALLED', true);

		$this->_config = new Config(ROOT_PATH . '/app/config/core.ini');

		$namespaces = [];
		$namespaces[__NAMESPACE__] = dirname(__DIR__).'/Classes';
		$namespaces[__NAMESPACE__.'\Models'] = dirname(__DIR__).'/Models';

		$loader = new Loader();

		$loader->registerNamespaces($namespaces);
		$loader->register();

		$di = new DI\FactoryDefault();

		$registry = new Registry();

		/** @noinspection PhpUndefinedFieldInspection */
		$registry->directories = (object)[
			'modules' => ROOT_PATH.$this->_config->application->baseDir.'modules/',
		];

		$di->set('loader', $loader, true);
		$di->set('registry', $registry, true);
		$di->set('config', $this->_config, true);

		parent::__construct($di);
	}

	public function run ()
	{
		$di = $this->getDI();
		$di->set('app', $this, true);

		$eventsManager = new EventsManager();
		$this->setEventsManager($eventsManager);

		if (defined('INSTALLED'))
		{
			if (file_exists(ROOT_PATH.$this->_config->application->baseDir.'globals.php'))
				include_once(ROOT_PATH.$this->_config->application->baseDir.'globals.php');

			$this->initProfiler($di, $eventsManager);

			if ($di->has('profiler'))
				$appBenchmark = $di->getShared('profiler')->start(__CLASS__.'::run', [], 'Application');

			$this->initDatabase($di, $eventsManager);

			$registry = $di->get('registry');

			/** @noinspection PhpUndefinedFieldInspection */
			$registry->modules = Module::find()->toArray();

			if ($this->request->hasQuery('clear_cache'))
				CacheHelper::clearAll();

			$this->initModules($di);
			$this->initLoaders($di);

			foreach ($this->_loaders as $service)
			{
				$serviceName = ucfirst($service);

				if ($di->has('profiler'))
					$benchmark = $di->getShared('profiler')->start(__CLASS__.'::init'.$serviceName, [], 'Application');

				$eventsManager->fire('init:before'.$serviceName, null);
				$result = $this->{'init'.$serviceName}($di, $eventsManager);
				$eventsManager->fire('init:after'.$serviceName, $result);

				if ($di->has('profiler') && isset($benchmark))
					$di->getShared('profiler')->stop($benchmark);
			}

			if ($di->has('profiler') && isset($appBenchmark))
				$di->getShared('profiler')->stop($appBenchmark);
		}

		$di->set('eventsManager', $eventsManager, true);
	}

	public function getOutput()
	{
		if (defined('INSTALLED') && $this->getDI()->has('profiler'))
		{
			$benchmark = $this->getDI()->getShared('profiler')->start(__CLASS__.'::getOutput', [], 'Application');

			$this->assets->addCss('//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
		}

		$handle = $this->handle();

		if (isset($benchmark))
			$this->getDI()->getShared('profiler')->stop($benchmark);

		if (defined('INSTALLED') && $this->_config->application->profiler && !$this->request->isAjax() && !$this->view->isDisabled())
		{
			$controller = $this->router->getControllerName();

			if ($controller !== '')
			{
				$toolbar = new Prophiler\Toolbar($this->getDI()->getShared('profiler'));
				$toolbar->addDataCollector(new Prophiler\DataCollector\Request());
				$toolbar->addDataCollector(new Debug\Profiler\Data\Files());
				$toolbar->addDataCollector(new Debug\Profiler\Data\ApcCache());
			}
		}

		$this->eventsManager->fire('core:beforeOutput', $this, $handle);

		return $handle->getContent().(isset($toolbar) && defined('SUPERUSER') ? $toolbar->render() : '');
	}

	/**
	 * @param $di DiInterface
	 * @return void
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

		parent::registerModules($modules);
	}

	public function hasModule ($name)
	{
		return (isset($this->_modules[Text::lower($name)]));
	}
}