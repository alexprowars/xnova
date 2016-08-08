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
	private $mode = '';
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

		Lang::setLang($this->config->app->language, 'xnova');
		Lang::includeLang('admin', 'xnova');

		if ($this->request->isAjax())
			$this->view->disableLevel(View::LEVEL_MAIN_LAYOUT);
		else
		{
			$this->tag->setTitleSeparator(' | ');
			$this->tag->setTitle(Options::get('site_title'));
			$this->tag->setDocType(Tag::HTML5);
		}

		$this->assets->addCss('https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all');
		$this->assets->addCss('assets/admin/global/plugins/font-awesome/css/font-awesome.css');
		$this->assets->addCss('assets/admin/global/plugins/simple-line-icons/simple-line-icons.css');
		$this->assets->addCss('assets/admin/global/plugins/bootstrap/css/bootstrap.css');
		$this->assets->addCss('assets/admin/global/plugins/bootstrap-switch/css/bootstrap-switch.css');
		$this->assets->addCss('assets/admin/global/plugins/bootstrap-select/css/bootstrap-select.css');
		$this->assets->addCss('assets/admin/global/css/components.css', ['sort' => 1000]);
		$this->assets->addCss('assets/admin/global/css/plugins.css', ['sort' => 1001]);
		$this->assets->addCss('assets/admin/pages/css/layout.css', ['sort' => 1002]);
		$this->assets->addCss('assets/admin/pages/css/darkblue.css', ['sort' => 1003]);
		$this->assets->addCss('assets/admin/pages/css/custom.css', ['sort' => 1004]);

		$this->assets->addJs('assets/admin/global/plugins/jquery.min.js');
		$this->assets->addJs('assets/admin/global/plugins/bootstrap/js/bootstrap.js');
		$this->assets->addJs('assets/admin/global/plugins/js.cookie.min.js');
		$this->assets->addJs('assets/admin/global/plugins/jquery.blockui.min.js');
		$this->assets->addJs('assets/admin/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js');
		$this->assets->addJs('assets/admin/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.js');
		$this->assets->addJs('assets/admin/global/plugins/bootstrap-switch/js/bootstrap-switch.js');
		$this->assets->addJs('assets/admin/global/plugins/bootstrap-select/js/bootstrap-select.js');
		$this->assets->addJs('assets/admin/global/js/app.js', ['sort' => 10]);

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

		if (!is_array($menu))
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

				$class = ($module['system'] == VALUE_TRUE ? 'Friday\\' : '').$moduleName.'\Controllers\\'.str_replace('.php', '', $file->getFilename());

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