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
 * @var $loader Loader
 */

$config = $di->getShared('config');

$loader->registerClasses([
		'Xnova\Database' => ROOT_PATH.$config->application->baseDir.'modules/Xnova/Classes/Database.php'
], true);

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

/** @noinspection PhpUnusedParameterInspection */
$eventsManager->attach('core:beforeOutput', function ($event, \Friday\Core\Application $app, Phalcon\Http\Response $handle)
{
	if ($app->dispatcher->getModuleName() == 'admin')
		return;

	if ($app->request->isAjax())
	{
		\Xnova\Request::addData('html', $handle->getContent());

		/** @noinspection PhpUndefinedFieldInspection */
		$app->response->setJsonContent(
		[
			'status' 	=> \Xnova\Request::getStatus(),
			'data' 		=> \Xnova\Request::getData()
		]);
		$app->response->setContentType('text/json', 'utf8');
		$app->response->send();
		die();
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

	$compiler->addFunction('_text', function($arguments)
	{
		return '\Friday\Core\Lang::getText(' . $arguments . ')';
	});

	$compiler->addFunction('plural', function($arguments)
	{
		return '\Friday\Core\Helpers::getPlural(' . $arguments . ')';
	});

	$compiler->addFilter('floor', 'floor');
	$compiler->addFilter('round', 'round');
	$compiler->addFilter('ceil', 'ceil');
	$compiler->addFunction('in_array', 'in_array');

	$compiler->addFunction('allowMobile', function($arguments)
	{
		return 'class_exists("\Xnova\Helpers") && \Xnova\Helpers::allowMobileVersion(' . $arguments . ')';
	});

	$compiler->addFunction('toJson', 'json_encode');
	$compiler->addFunction('replace', 'str_replace');
	$compiler->addFunction('preg_replace', 'preg_replace');
	$compiler->addFunction('md5', 'md5');
	$compiler->addFunction('min', 'min');
	$compiler->addFunction('max', 'max');
	$compiler->addFunction('floor', 'floor');
	$compiler->addFunction('ceil', 'ceil');
	$compiler->addFunction('array_search', 'array_search');
	$compiler->addFunction('is_email', 'is_email');
	$compiler->addFunction('htmlspecialchars', 'htmlspecialchars');
	$compiler->addFunction('rand', 'mt_rand');
	$compiler->addFunction('implode', 'implode');
	$compiler->addFunction('pretty_number', function($arguments)
	{
		return '\Xnova\Format::number(' . $arguments . ')';
	});
	$compiler->addFunction('pretty_time', function($arguments)
	{
		return '\Xnova\Format::time(' . $arguments . ')';
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
		return '\Xnova\Building::isTechnologieAccessible(' . $arguments . ')';
	});
	$compiler->addFunction('colorNumber', function($arguments)
	{
		return '\Xnova\Helpers::colorNumber(' . $arguments . ')';
	});
	$compiler->addFunction('planetLink', function($arguments)
	{
		return '\Xnova\Helpers::BuildPlanetAdressLink(' . $arguments . ')';
	});
});

/** @noinspection PhpUnusedParameterInspection */
$eventsManager->attach("dispatch:beforeException", function($event, $dispatcher, $exception)
{
	/**
	 * @var \Phalcon\Mvc\Dispatcher $dispatcher
	 * @var \Phalcon\Mvc\Dispatcher\Exception $exception
	 */
	switch ($exception->getCode())
	{
		case Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
		case Dispatcher::EXCEPTION_ACTION_NOT_FOUND:

			if ($dispatcher->getControllerName() == $dispatcher->getPreviousControllerName() && $dispatcher->getActionName() == $dispatcher->getPreviousActionName())
				return true;

			$dispatcher->forward([
				'module'		=> 'xnova',
				'controller'	=> 'error',
				'action'		=> 'notFound',
				'namespace'		=> 'Xnova\Controllers'
			]);

			return false;

		case 10:

			$params = unserialize($exception->getMessage());

			$dispatcher->forward([
				'module'		=> 'xnova',
				'controller'	=> $params['controller'],
				'action'		=> $params['action'],
				'namespace'		=> 'Xnova\Controllers'
			]);
	}

	return true;
});

define('VERSION', '3.2');