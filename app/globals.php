<?php

use Phalcon\DiInterface;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Loader;
use Friday\Core\Auth\Auth;
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

define('VERSION', '3.0.3');