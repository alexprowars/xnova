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
use Friday\Core\Prophiler;
use Phalcon\Mvc\Url as UrlResolver;
use Friday\Core\Helpers\Cache as CacheHelper;
use Phalcon\Text;

include_once(ROOT_PATH."/app/functions.php");

define('VALUE_TRUE', 'Y');
define('VALUE_FALSE', 'N');

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
		$di = new DI\FactoryDefault();

		$registry = new Registry();
		$this->_config = new Config(ROOT_PATH . '/app/config/core.ini');

		/** @noinspection PhpUndefinedFieldInspection */
		$registry->directories = (object)[
			'modules' => ROOT_PATH.$this->_config->application->baseDir.'modules/',
		];

		$di->set('registry', $registry);
		$di->setShared('config', $this->_config);

		parent::__construct($di);
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

		if (file_exists(ROOT_PATH.$this->_config->application->baseDir.'globals.php'))
			include_once(ROOT_PATH.$this->_config->application->baseDir.'globals.php');

		$this->initProfiler($di, $eventsManager);

		if ($this->_config->application->prophiler)
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

			if ($this->_config->application->prophiler)
				$benchmark = $di->getShared('profiler')->start(__CLASS__.'::init'.$serviceName, [], 'Application');

			$eventsManager->fire('init:before'.$serviceName, null);
			$result = $this->{'init'.$serviceName}($di, $eventsManager);
			$eventsManager->fire('init:after'.$serviceName, $result);

			if ($this->_config->application->prophiler && isset($benchmark))
				$di->getShared('profiler')->stop($benchmark);
		}

		$di->set('eventsManager', $eventsManager, true);

		if ($this->_config->application->prophiler && isset($appBenchmark))
			$di->getShared('profiler')->stop($appBenchmark);
	}

	public function getOutput()
	{
		if ($this->_config->application->prophiler && $this->getDI()->has('profiler'))
		{
			$benchmark = $this->getDI()->getShared('profiler')->start(__CLASS__.'::getOutput', [], 'Application');

			$this->assets->addCss('//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css');
		}

		$handle = $this->handle();

		if (isset($benchmark))
			$this->getDI()->getShared('profiler')->stop($benchmark);

		if ($this->_config->application->prophiler && !$this->request->isAjax() && !$this->view->isDisabled())
		{
			$controller = $this->router->getControllerName();

			if ($controller !== '')
			{
				$toolbar = new Prophiler\Toolbar($this->getDI()->getShared('profiler'));
				$toolbar->addDataCollector(new Prophiler\DataCollector\Request());
				$toolbar->addDataCollector(new Prophiler\DataCollector\Files());
			}
		}

		return $handle->getContent().(isset($toolbar) ? $toolbar->render() : '');
	}

	/**
	 * @param $di DiInterface
	 * @param $config \Phalcon\Config|\stdClass
	 * @param $eventsManager
	 * @return Loader
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

				$modules[mb_strtolower($module['code'])] = [
					'className'	=> ($module['system'] == VALUE_TRUE ? 'Friday\\' : '').ucfirst($module['code']).'\Module',
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