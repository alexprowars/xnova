<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

if (!extension_loaded('phalcon'))
	dl('phalcon.so');

use App\Database;
use App\Game;
use App\Lang;
use Phalcon\Di\FactoryDefault\Cli as CliDI;
use Phalcon\Cli\Console as ConsoleApp;
use Phalcon\Cache\Backend\Memcache as Cache;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File as FileLogger;
use Phalcon\Events\Manager as EventsManager;

$di = new CliDI();

define('APP_PATH', dirname(__DIR__.'../') . '/');

ini_set('log_errors', 'On');
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('error_log', APP_PATH.'/php_errors.log');

if (is_readable(APP_PATH . '/app/config/config.ini'))
{
	$config = new \Phalcon\Config\Adapter\Ini(APP_PATH . '/app/config/config.ini');

    $di->set('config', $config);

	include (APP_PATH . '/app/config/loader.php');
	$loader->registerDirs([APP_PATH . '/app/tasks']);

	$di->set(
	    'db', function () use ($config)
		{
			if (!file_exists(APP_PATH."/app/logs/debug_".date("d.m.Y-H").".log"))
				fclose(fopen(APP_PATH."/app/logs/debug_".date("d.m.Y-H").".log", "w"));

			$logger = new FileLogger(APP_PATH."/app/logs/debug_".date("d.m.Y-H").".log");

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
				}
			});

			/**
			 * @var Object $config
			 */
			$connection = new Database([
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
	    'cache', function () use ($config, $di)
		{
			$frontCache = new \Phalcon\Cache\Frontend\None(["lifetime" => 3600]);

			/**
			 * @var Object $config
			 */
			$cache = new Cache($frontCache, [
				"host" => $config->memcache->host,
				"port" => $config->memcache->port
			]);

	        return $cache;
	    }, true
	);

	$di->setShared(
		'storage', function ()
		{
			$registry = new \Phalcon\Registry();
			return $registry;
		}
	);

	$di->set('game', function ()
	{
	    return new Game();
	});
}
else
	die('config.ini not found');

Lang::setLang($di->get('config')->app->language);

$console = new ConsoleApp();
$console->setDI($di);

include (APP_PATH . '/app/config/bootstrap.php');
include_once(APP_PATH."/app/config/battle.php");

$console->getDI()->getShared('game')->loadGameVariables();

$arguments = [];

foreach ($argv as $k => $arg)
{
    if ($k == 1)
        $arguments['task'] = $arg;
	elseif ($k == 2)
        $arguments['action'] = $arg;
	elseif ($k >= 3)
        $arguments['params'][] = $arg;
}

define('CURRENT_TASK',   (isset($argv[1]) ? $argv[1] : null));
define('CURRENT_ACTION', (isset($argv[2]) ? $argv[2] : null));

try
{
    $console->handle($arguments);
}
catch (\Phalcon\Exception $e)
{
    echo $e->getMessage()."\n";
    exit(255);
}