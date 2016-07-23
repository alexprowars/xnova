<?php
namespace Xnova\Admin\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use App\Lang;
use Phalcon\Mvc\Controller;

/**
 * Class ControllerBase
 * @property \Phalcon\Mvc\View view
 * @property \Phalcon\Tag tag
 * @property \Phalcon\Assets\Manager assets
 * @property \App\Database db
 * @property \Phalcon\Mvc\Model\Manager modelsManager
 * @property \Phalcon\Session\Adapter\Memcache session
 * @property \Phalcon\Http\Response\Cookies cookies
 * @property \Phalcon\Http\Request request
 * @property \Phalcon\Http\Response response
 * @property \Phalcon\Mvc\Router router
 * @property \Phalcon\Cache\Backend\Memcache cache
 * @property \Phalcon\Mvc\Url url
 * @property \App\Models\User user
 * @property \App\Models\Planet planet
 * @property \App\Auth\Auth auth
 * @property \Phalcon\Mvc\Dispatcher dispatcher
 * @property \Phalcon\Flash\Direct flash
 * @property \Phalcon\Registry|\stdClass storage
 * @property \Phalcon\Config|\stdClass config
 */
class Application extends Controller
{
	private $modules = [];
	private $mode = '';

	public function initialize ()
	{
		if ($this->dispatcher->wasForwarded() && $this->dispatcher->getControllerName() !== 'error')
			return;

		Lang::setLang($this->config->app->language);
		Lang::includeLang('admin');

		if (!$this->auth->isAuthorized())
		{
			$auth = $this->auth->check();

			if ($auth !== false)
				$this->getDI()->set('user', $auth);
		}

		if (!$this->getDI()->has('user'))
			$this->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));

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

		$result = $this->db->query("SELECT m.id, m.alias, m.name, r.right_id FROM game_cms_modules m LEFT JOIN game_cms_rights r ON r.module_id = m.id AND r.group_id = ".$this->user->group_id." AND right_id != '0' WHERE m.is_admin = '1' AND m.active = '1'");

		while ($r = $result->fetch())
		{
			$this->modules[mb_strtolower($r['alias'], 'utf-8')] =
			[
				'id' 	=> $r['id'],
				'alias'	=> $r['alias'],
				'name' 	=> $r['name'],
				'right' => $this->user->isAdmin() ? 2 : (!$r['right_id'] ? 0 : $r['right_id'])
			];
		}

		$menu = $this->getMenu(1, 2);

		foreach ($menu AS $i => $item)
		{
			if (!isset($this->modules[$item['alias']]) || !$this->modules[$item['alias']]['right'])
				unset($menu[$i]);
		}

		$this->mode = mb_strtolower($this->dispatcher->getControllerName());

		if ((!isset($this->modules[$this->mode]) || $this->modules[$this->mode]['right'] < 0) && $this->mode != 'error')
			$this->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));

		$this->view->setVar('menu', $menu);
		$this->view->setVar('mode', $this->mode);
		$this->view->setVar('userId', $this->user->getId());
		$this->view->setVar('adminlevel', $this->user->authlevel);

		$this->game->loadGameVariables();
	}

	public function getMenu ($parent_id, $lvl = 1, $all = false)
	{
		$array = [];

		if ($lvl > 0)
		{
			$childrens = $this->db->extractResult($this->db->query("SELECT id, name, alias, icon, image, active FROM game_cms_menu WHERE parent_id = ".intval($parent_id)." ".($all ? '' : "AND active = '1'")." ORDER BY priority ASC"));

			if (count($childrens) > 0)
			{
				foreach ($childrens AS $children)
				{
					$array[] = [
						'id' 		=> $children['id'],
						'alias' 	=> mb_strtolower($children['alias'], 'utf-8'),
						'name' 		=> $children['name'],
						'children' 	=> ($lvl > 1) ? $this->getMenu($children['id'], ($lvl - 1), $all) : [],
						'active' 	=> $children['active'],
						'icon' 		=> $children['icon'],
						'image' 	=> $children['image']
					];
				}
			}
		}

		return $array;
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
}