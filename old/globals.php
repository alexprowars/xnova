<?php

use Phalcon\DiInterface;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Loader;
use Friday\Core\Auth\Auth;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\View\Engine\Volt;
use Xnova\Models\User;

/**
 * @var $di DiInterface
 * @var $eventsManager EventsManager
 */

$config = $di->getShared('config');
$loader = $di->getShared('loader');

/** @noinspection PhpUnusedParameterInspection */
$eventsManager->attach('core:beforeAuthCheck', function ($event, Auth $auth)
{
	\Friday\Core\Modules::init('xnova');

	if (!$auth->isAuthorized())
	{
		$auth->addPlugin('\Xnova\Auth\Plugins\Ulogin');
		$auth->addPlugin('\Xnova\Auth\Plugins\Vk');
	}
});

$eventsManager->attach('core:beforeStartSession', function ()
{
	if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'python-requests') !== false)
		return false;

	return true;
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