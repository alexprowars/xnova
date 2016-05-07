<?php

use App\Auth\Auth;
use App\Auth\Security;
use App\Database;
use App\Game;
use Phalcon\Crypt;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Logger;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\Model;
use Phalcon\Cache\Backend\Memcache as Cache;
use Phalcon\Logger\Adapter\File as FileLogger;

$di = new FactoryDefault();

$di->setShared('cookies', function()
{
	$cookies = new Phalcon\Http\Response\Cookies();
	$cookies->useEncryption(false);

	return $cookies;
});

$di->setShared('router', function ()
{
	return require __DIR__ . '/routes.php';
});

$di->setShared(
	'url', function () use ($config)
	{
		/**
		 * @var Object $config
		 */
		$url = new UrlResolver();
		$url->setBaseUri($config->application->baseUri);
		return $url;
	}
);

$di->setShared(
	'db', function () use ($config)
	{
		if (!file_exists(APP_PATH."app/logs/debug_".date("d.m.Y-H").".log"))
			fclose(fopen(APP_PATH."app/logs/debug_".date("d.m.Y-H").".log", "w"));

		$logger = new FileLogger(APP_PATH."app/logs/debug_".date("d.m.Y-H").".log");

		$eventsManager = new EventsManager();

		$eventsManager->attach('db', function(Phalcon\Events\Event $event, App\Database $connection) use ($logger)
		{
			if ($event->getType() == 'beforeQuery')
			{
				$logger->log($connection->getSQLStatement()."\n".print_r($connection->getSqlVariables(), true), Logger::INFO);

				$d = debug_backtrace();

				foreach ($d as $a)
				{
					if (isset($a['file']))
						$logger->log("".(isset($a['class']) ? $a['class'] : '').'::'.$a['function'].''.(isset($a['line']) ? ' in file '.$a['file'].' on line '.$a['line'].'' : ''), Logger::DEBUG);
				}

				$logger->log("\n", Logger::INFO);
			}
		});

		/**
		 * @var Object $config
		 */
		$connection = new Database(
		[
			'host' 		=> $config->database->host,
			'username' 	=> $config->database->username,
			'password' 	=> $config->database->password,
			'dbname' 	=> $config->database->dbname,
			'options' 	=> [PDO::ATTR_PERSISTENT => false, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
		]);

		$connection->setEventsManager($eventsManager);

		return $connection;
	}
);

$di->set(
	'session', function ()
	{
		$session = new SessionAdapter();
		$session->start();
		return $session;
	}
);

$di->set('dispatcher', function () use ($di)
{
	$eventsManager = new EventsManager;
	$eventsManager->attach('dispatch:beforeExecuteRoute', new Security);
	/** @noinspection PhpUnusedParameterInspection */
	$eventsManager->attach("dispatch:beforeException", function($event, $dispatcher, $exception)
	{
		/**
		 * @var Phalcon\Mvc\Dispatcher $dispatcher
		 * @var Phalcon\Mvc\Dispatcher\Exception $exception
		 */
		switch ($exception->getCode())
		{
			case Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
			case Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
				$dispatcher->forward([
					'controller' => 'error',
					'action'	 => 'notFound',
				]);
				return false;
		}

		return true;
	});

	$dispatcher = new Dispatcher();
	$dispatcher->setDefaultNamespace('App\Controllers');
	$dispatcher->setEventsManager($eventsManager);
	return $dispatcher;
});

$di->setShared('auth', function ()
{
	return new Auth();
});

$di->setShared('game', function ()
{
	return new Game();
});

$di->remove('transactionManager');
//$di->remove('flashSession');
$di->remove('flash');
$di->remove('annotations');

$di->setShared('config', $config);

$di->setShared('crypt', function()
{
	$crypt = new Crypt();
	$crypt->setKey('fsdgdghrdfhgasdfsd');
	return $crypt;
});

$di->setShared(
	'cache', function () use ($config, $di)
	{
		$frontCache = new \Phalcon\Cache\Frontend\None(["lifetime" => 3600]);

		/**
		 * @var Object $config
		 */
		$cache = new Cache($frontCache,
		[
			"host" => $config->memcache->host,
			"port" => $config->memcache->port
		]);

		if ($di->has('profiler'))
		{
			$profiler = $di->get('profiler');

			/** @noinspection PhpUndefinedNamespaceInspection */
			/** @noinspection PhpUndefinedClassInspection */
			$cache = new \Fabfuel\Prophiler\Decorator\Phalcon\Cache\BackendDecorator($cache, $profiler);
		}

		return $cache;
	}
);

$di->set('modelsMetadata', function()
{
	$metaData = new \Phalcon\Mvc\Model\MetaData\Memcache([
		'lifetime' 		=> 3600,
		'prefix'  		=> 'xnova',
		'host' 			=> 'localhost',
		'port' 			=> 11211,
		'persistent' 	=> false,
	]);

	return $metaData;
});

$di->setShared(
	'storage', function ()
	{
		$registry = new \Phalcon\Registry();
		return $registry;
	}
);

Model::setup([
	'events' 			=> true,
	'columnRenaming' 	=> false,
	'notNullValidations'=> false,
]);