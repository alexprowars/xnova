<?php

use Phalcon\DiInterface;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Loader;
use Friday\Core\Auth\Auth;
use Phalcon\Mvc\View\Engine\Volt;
use Xnova\Models\User;

/**
 * @var $di DiInterface
 * @var $eventsManager EventsManager
 * @var $loader Loader
 */

$config = $di->getShared('config');

$loader->registerClasses([
		'Xnova\Database' => ROOT_PATH.$config->application->baseDir.'modules/Xnova/Classes/Database.php'
], true);

/** @noinspection PhpUnusedParameterInspection */
$eventsManager->attach('core:beforeAuthCheck', function ($event, Auth $auth)
{
	if (!$auth->isAuthorized())
	{
		$auth->addPlugin('\Xnova\Auth\Plugins\Ulogin');
		$auth->addPlugin('\Xnova\Auth\Plugins\Vk');
		$auth->addPlugin('\Xnova\Auth\Plugins\Ok');
	}
});

/** @noinspection PhpUnusedParameterInspection */
$eventsManager->attach('core:afterAuthCheck', function ($event, Auth $auth, User $user) use ($di)
{
	if ($di->getShared('router')->getControllerName() != 'banned')
	{
		$game = $di->getShared('game');
		$url = $di->getShared('url');

		if ($user->banned > time())
			die('Ваш аккаунт заблокирован. Срок окончания блокировки: '.$game->datezone("d.m.Y H:i:s", $user->banned).'<br>Для получения дополнительной информации зайдите <a href="'.$url->get('banned/').'">сюда</a>');
		elseif ($user->banned > 0 && $user->banned < time())
		{
			$this->db->delete('game_banned', 'who = ?', [$user->id]);
			$this->db->updateAsDict('game_users', ['banned' => 0], 'id = '.$user->id);

			$user->banned = 0;
		}
	}
});

/** @noinspection PhpUnusedParameterInspection */
$eventsManager->attach('view:afterEngineRegister', function ($event, Volt $volt)
{
	$compiler = $volt->getCompiler();

	$compiler->addFunction('allowMobile', function($arguments)
	{
		return 'class_exists("\Xnova\Helpers") && \Xnova\Helpers::allowMobileVersion(' . $arguments . ')';
	});

	$compiler->addFunction('replace', 'str_replace');
	$compiler->addFunction('preg_replace', 'preg_replace');
	$compiler->addFunction('md5', 'md5');
	$compiler->addFunction('pretty_number', function($arguments)
	{
		return '\Xnova\Helpers::pretty_number(' . $arguments . ')';
	});
	$compiler->addFunction('pretty_time', function($arguments)
	{
		return '\Xnova\Helpers::pretty_time(' . $arguments . ')';
	});
	$compiler->addFunction('option', function($arguments)
	{
		return '\Friday\Core\Options::get(' . $arguments . ')';
	});
	$compiler->addFunction('getTechTree', function($arguments)
	{
		return '\Xnova\Building::getTechTree(' . $arguments . ')';
	});
	$compiler->addFunction('isTechnologieAccessible', function($arguments)
	{
		return '\Xnova\Building::IsTechnologieAccessible(' . $arguments . ')';
	});
});

define('VERSION', '3.0.3');