<?php

namespace Xnova\Bot\Controllers;

use App\Lang;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\View;
use Phalcon\Tag;

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
class Application extends Controller
{
	static private $isInitialized = false;

	protected function initialize()
	{
		if (self::$isInitialized)
			return;

		self::$isInitialized = true;

		Lang::setLang($this->config->app->language);
	}

	public function notFoundAction ()
	{
		$this->response->setStatusCode(404);
		$this->response->setJsonContent(['error' => 'Not Found']);
		$this->response->send();

		die();
	}
}