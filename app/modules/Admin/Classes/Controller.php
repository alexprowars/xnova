<?php

namespace Admin;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use DirectoryIterator;
use Friday\Core\Lang;
use Friday\Core\Options;
use Phalcon\Mvc\Controller as PhalconController;
use Phalcon\Mvc\View;
use Phalcon\Tag;
use Xnova\Vars;

/**
 * Class ControllerBase
 * @property \Phalcon\Mvc\View view
 * @property \Phalcon\Tag tag
 * @property \Phalcon\Assets\Manager assets
 * @property \Xnova\Database db
 * @property \Phalcon\Mvc\Model\Manager modelsManager
 * @property \Phalcon\Session\Adapter\Memcache session
 * @property \Phalcon\Http\Response\Cookies cookies
 * @property \Phalcon\Http\Request request
 * @property \Phalcon\Http\Response response
 * @property \Phalcon\Mvc\Router router
 * @property \Phalcon\Cache\Backend\Memcache cache
 * @property \Phalcon\Mvc\Url url
 * @property \Xnova\Models\User user
 * @property \Xnova\Models\Planet planet
 * @property \Friday\Core\Auth\Auth auth
 * @property \Phalcon\Mvc\Dispatcher dispatcher
 * @property \Phalcon\Flash\Direct flash
 * @property \Phalcon\Registry|\stdClass registry
 * @property \Phalcon\Config|\stdClass config
 * @property \Friday\Core\Access access
 */
class Controller extends PhalconController
{
	private $modules = [];
	private $breadcrumbs = [];

	const CACHE_KEY_MENU = 'MENU';
	const CACHE_TIME_MENU = 3600;

	static private $isInitialized = false;

	public function initialize ()
	{
		if (self::$isInitialized)
			return false;

		self::$isInitialized = true;

		if (!$this->auth->isAuthorized())
			return $this->response->redirect('');

		$this->url->setBaseUri('/admin/');

		Lang::setLang($this->config->app->language, 'admin');
		Lang::includeLang($this->dispatcher->getControllerName(), 'admin');

		if (!$this->access->hasAccess('admin'))
			throw new \Exception('Access denied');

		if ($this->request->isAjax())
			$this->view->disableLevel(View::LEVEL_MAIN_LAYOUT);
		else
		{
			$this->tag->setTitleSeparator(' | ');
			$this->tag->setTitle(Options::get('site_title'));
			$this->tag->setDocType(Tag::HTML5);
		}

		if (is_array($this->router->getParams()) && count($this->router->getParams()))
		{
			$params = $this->router->getParams();

			foreach ($params as $key => $value)
			{
				if (!is_numeric($key))
				{
					$_REQUEST[$key] = $_GET[$key] = $value;

					unset($params[$key]);
				}
			}

			$params = array_values($params);

			for ($i = 0; $i < count($params); $i += 2)
			{
				if (isset($params[$i]) && $params[$i] != '' && !is_numeric($params[$i]))
					$_REQUEST[$params[$i]] = $_GET[$params[$i]] = (isset($params[$i+1])) ? $params[$i+1] : '';
			}
		}

		Vars::init();

		$this->assets->addCss('https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,300i');
		$this->assets->addCss('assets/admin/plugins/bootstrap/bootstrap.css');
		$this->assets->addCss('assets/admin/plugins/material-icons/material-icons.css');
		$this->assets->addCss('assets/admin/plugins/themify-icons/css/themify-icons.css');
		$this->assets->addCss('assets/admin/plugins/font-awesome/css/font-awesome.css');
		$this->assets->addCss('assets/admin/plugins/datatables/css/dataTables.bootstrap4.css');
		$this->assets->addCss('assets/admin/plugins/perfect-scrollbar/css/perfect-scrollbar.css');
		$this->assets->addCss('assets/build/admin/admin.css');

		$this->assets->addJs('assets/admin/plugins/jquery/jquery.js');
		$this->assets->addJs('assets/admin/plugins/popper/popper.js');
		$this->assets->addJs('assets/admin/plugins/bootstrap/bootstrap.js');
		//$this->assets->addJs('assets/admin/plugins/bootstrap-validator/validator-bs4.js');
		$this->assets->addJs('assets/admin/plugins/datatables/js/jquery.dataTables.js');
		$this->assets->addJs('assets/admin/plugins/datatables/js/dataTables.bootstrap4.js');
		$this->assets->addJs('assets/admin/plugins/perfect-scrollbar/js/perfect-scrollbar.jquery.js');

		$this->assets->addJs('assets/build/runtime.js');
		$this->assets->addJs('assets/build/admin/app.js');

		return true;
	}

	public function afterExecuteRoute ()
	{
		if ($this->view->isDisabled())
			return;

		$this->tag->appendTitle(Options::get('site_title'));
		$this->view->setVar('route_controller', $this->dispatcher->getControllerName());
		$this->view->setVar('route_action', $this->dispatcher->getActionName());

		$this->view->setVar('user_id', $this->user->getId());
		$this->view->setVar('user_name', $this->user->username);
		$this->view->setVar('user_full_name', $this->user->username);
		$this->view->setVar('user_photo', "");

		$menu = $this->cache->get(self::CACHE_KEY_MENU.'_'.$this->user->authlevel);

		//if (!is_array($menu))
		{
			$menu = $this->generateMenu();

			$this->cache->save(self::CACHE_KEY_MENU.'_'.$this->user->authlevel, $menu, self::CACHE_TIME_MENU);
		}

		$this->view->setVar('main_menu', $menu);

		//$notifications = Notification::find(["order" => "time DESC", "conditions" => "user_id = :user: AND viewed = 0", "bind" => ["user" => $this->user->getId()]]);

		$this->view->setVar('notifications', []);

		$this->view->setVar('breadcrumbs', $this->getBreadcrumbs());
	}

	public static function getMenu()
	{
		return [];
	}

	public function addToBreadcrumbs ($title, $url = '')
	{
		$this->breadcrumbs[] = [
			'url' 	=> trim($url, '/ ').'/',
			'title' => trim($title)
		];
	}

	public function getBreadcrumbs ()
	{
		return $this->breadcrumbs;
	}

	public function message ($text, $title = '', $redirect = '', $timeout = 5)
	{
		$this->view->pick('shared/message');
		$this->view->setVar('text', $text);
		$this->view->setVar('title', $title);
		$this->view->setVar('destination', $redirect);
		$this->view->setVar('time', $timeout);

		$this->tag->setTitle(($title ? strip_tags($title) : 'Сообщение'));

		/** @var \Xnova\Controller $controller */
		$controller = $this->dispatcher->getActiveController();

		if ($controller)
			$controller->afterExecuteRoute();

		$this->view->start();

		$this->view->render(
			$this->dispatcher->getControllerName(),
			$this->dispatcher->getActionName(),
			$this->dispatcher->getParams()
		);

		$this->view->finish();

		echo $this->view->getContent();

		die();
	}

	public function notFoundAction()
	{
		$this->assets->addCss('assets/admin/pages/css/error.css');

		$this->view->setMainView('error');
		$this->view->enable();

		$this->response->setStatusCode(404, 'Not Found');
	}

	private function generateMenu ()
	{
		$menu = [];

		$registry = $this->registry;

		foreach ($registry->modules as $module)
		{
			$moduleName = ucfirst($module['code']);

			if (!file_exists($registry->directories->modules.$moduleName.'/Controllers'))
				continue;

			$files = new DirectoryIterator($registry->directories->modules.$moduleName.'/Controllers');

			foreach ($files as $file)
			{
				if (!$file->isFile() || strpos($file->getFilename(), 'Controller.php') === false)
					continue;

				if (!$this->access->hasAccess($module['code']))
					continue;

				$class = ($module['namespace'] != '' ? $module['namespace'] : $moduleName).'\Controllers\\'.str_replace('.php', '', $file->getFilename());

				if (method_exists($class, 'getMenu'))
				{
					$items = $class::getMenu();

					if (!isset($items[0]))
						$items = [$items];

					foreach ($items as $item)
					{
						if (!isset($item['title']) || empty($item['title']) || !isset($item['code']) || empty($item['code']))
							continue;

						if ($this->access->canReadController($item['code'], $module['code']))
						{
							if (!isset($item['icon']))
								$item['icon'] = '';

							if (!isset($item['childrens']) || !is_array($item['childrens']))
								$item['childrens'] = [];

							if (!isset($item['sort']))
								$item['sort'] = '';

							$menu[] = [
								'code'		=> $item['code'],
								'title' 	=> $item['title'],
								'icon' 		=> $item['icon'],
								'sort' 		=> $item['sort'],
								'url' 		=> isset($item['url']) ? $item['url'] : false,
								'childrens'	=> $item['childrens']
							];
						}
					}
				}
			}
		}

		uasort($menu, function ($a, $b)
		{
			return ($a['sort'] > $b['sort'] ? 1 : -1);
		});

		return $menu;
	}
}