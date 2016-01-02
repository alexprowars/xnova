<?php

if (!extension_loaded('phalcon'))
	dl('phalcon.so');

use App\Database;
use App\Game;
use Phalcon\Di\FactoryDefault\Cli as CliDI;
use Phalcon\Cli\Console as ConsoleApp;
use Phalcon\Cache\Backend\Memcache as Cache;

$di = new CliDI();

define('APP_PATH', dirname(__DIR__.'../') . '/');

if (is_readable(APP_PATH . '/app/config/config.ini'))
{
	$config = new \Phalcon\Config\Adapter\Ini(APP_PATH . '/app/config/config.ini');

    $di->set('config', $config);

	include (APP_PATH . '/app/config/loader.php');
	$loader->registerDirs([APP_PATH . '/app/tasks']);

	$di->set(
	    'db', function () use ($config)
		{
			/**
			 * @var Object $config
			 */
			$connection = new Database(array
			(
	            'host' 		=> $config->database->host,
	            'username' 	=> $config->database->username,
	            'password' 	=> $config->database->password,
	            'dbname' 	=> $config->database->dbname,
				'options' 	=> [PDO::ATTR_PERSISTENT => false, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
	        ));

			return $connection;
	    }
	);

	$di->set(
	    'cache', function () use ($config, $di)
		{
			$frontCache = new \Phalcon\Cache\Frontend\Data(array(
				"lifetime" => 3600
			));

			/**
			 * @var Object $config
			 */
			$cache = new Cache($frontCache, array
			(
				"host" => $config->memcache->host,
				"port" => $config->memcache->port
			));

	        return $cache;
	    }, true
	);

	$di->set('game', function ()
	{
	    return new Game();
	});
}
else
	die('config.ini not found');

$console = new ConsoleApp();
$console->setDI($di);

$console->getDI()->getShared('game')->loadGameVariables();

$arguments = array();

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

?>