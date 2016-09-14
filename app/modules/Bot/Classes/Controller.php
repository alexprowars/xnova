<?php

namespace Bot;

use Friday\Core\Lang;
use Phalcon\Mvc\Controller as PhalconController;

/**
 * Class ControllerBase
 * @property \Phalcon\Mvc\View view
 * @property \Phalcon\Tag tag
 * @property \Phalcon\Assets\Manager assets
 * @property \Phalcon\Db\Adapter\Pdo\Mysql db
 * @property \Phalcon\Mvc\Model\Manager modelsManager
 * @property \Phalcon\Session\Adapter\Memcache session
 * @property \Phalcon\Http\Response\Cookies cookies
 * @property \Phalcon\Http\Request request
 * @property \Phalcon\Http\Response response
 * @property \Phalcon\Mvc\Router router
 * @property \Phalcon\Cache\Backend\Memcache cache
 * @property \Phalcon\Mvc\Url url
 * @property \Phalcon\Mvc\Dispatcher dispatcher
 * @property \Phalcon\Flash\Direct flash
 * @property \Phalcon\Config|\stdClass config
 */
class Controller extends PhalconController
{
	static private $isInitialized = false;

	protected function initialize()
	{
		if (self::$isInitialized)
			return;

		self::$isInitialized = true;

		Lang::setLang($this->config->app->language, 'xnova');

		file_put_contents(ROOT_PATH."/app/logs/telegram.log", print_r($_REQUEST, true), FILE_APPEND);
	}

	public function notFoundAction ()
	{
		$this->response->setStatusCode(404);
		$this->response->setJsonContent(['error' => 'Not Found']);
		$this->response->send();

		die();
	}
}