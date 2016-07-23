<?php

use Phalcon\DiInterface;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Loader;
use Friday\Core\Auth\Auth;

/**
 * @var $di DiInterface
 * @var $eventsManager EventsManager
 * @var $loader Loader
 */

$config = $di->getShared('config');

$loader->registerClasses([
		'Xnova\Database' => ROOT_PATH.$config->application->baseDir.'modules/Xnova/Classes/Database.php'
], true);

$eventsManager->attach('core:beforeAuthCheck', function ($event, Auth $auth)
{
	$auth->addPlugin('\Xnova\Auth\Plugins\Ulogin');
	$auth->addPlugin('\Xnova\Auth\Plugins\Vk');
	$auth->addPlugin('\Xnova\Auth\Plugins\Ok');
});

define('VERSION', '3.0.3');