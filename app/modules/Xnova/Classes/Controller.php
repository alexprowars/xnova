<?php

namespace Xnova;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Friday\Core\Lang;
use Friday\Core\Options;
use Phalcon\Mvc\Controller as PhalconController;
use Phalcon\Mvc\View;
use Phalcon\Tag;

/**
 * Class ControllerBase
 * @property \Phalcon\Mvc\View view
 * @property \Phalcon\Tag tag
 * @property \Friday\Core\Assets\Manager assets
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
 * @property \Xnova\Game game
 */
class Controller extends PhalconController
{
	static private $isInitialized = false;

	public $private = 0;

	private $showTopPanel = true;
	private $showLeftMenu = true;

	public function initialize()
	{
		if (self::$isInitialized)
			return true;

		if ($this->getDI()->has('game'))
			new \Exception('game module not initialized');

		self::$isInitialized = true;

		if (function_exists('sys_getloadavg'))
		{
			$load = sys_getloadavg();

			if ($load[0] > 15)
			{
				header('HTTP/1.1 503 Too busy, try again later');
				die('Server too busy. Please try again later.');
			}
		}

		Lang::setLang($this->config->app->language, 'xnova');

		if ($this->request->isAjax() && !$this->auth->isAuthorized())
			$this->view->disableLevel(View::LEVEL_MAIN_LAYOUT);
		else
		{
			$this->tag->setTitleSeparator(' :: ');
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

		$this->assets->addCss('assets/css/bootstrap.css?v='.VERSION);
		$this->assets->addCss('assets/css/jquery-ui.css');
		$this->assets->addCss('assets/css/jquery.fancybox.css');
		$this->assets->addCss('assets/css/style.css?v='.VERSION);

		$this->assets->addJs('https://cdn.jsdelivr.net/npm/vue');
		$this->assets->addJs('//ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js');
		$this->assets->addJs('//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js');
		$this->assets->addJs('assets/js/jquery.form.min.js');
		$this->assets->addJs('assets/js/jquery.fancybox.min.js');
		$this->assets->addJs('assets/js/game.js?v='.VERSION);

		Vars::init();

		if ($this->auth->isAuthorized())
		{
			//if (!$this->user->isAdmin())
			//	die('Нельзя пока вам сюда');

			$this->assets->addCss('assets/css/jquery.toast.min.css');
			$this->assets->addCss('assets/css/jquery.reject.css');

			$this->assets->addJs('assets/js/script.js?v='.VERSION);
			$this->assets->addJs('assets/js/universe.js?v='.VERSION);
			$this->assets->addJs('assets/js/flotten.js?v='.VERSION);
			$this->assets->addJs('assets/js/smiles.js?v='.VERSION);
			$this->assets->addJs('assets/js/ed.js?v='.VERSION);
			$this->assets->addJs('assets/js/jquery.touchSwipe.min.js');
			$this->assets->addJs('assets/js/jquery.toast.min.js');
			$this->assets->addJs('assets/js/jquery.mousewheel.min.js');
			$this->assets->addJs('assets/js/jquery.reject.js');

			if ($this->request->isAjax())
				$this->view->setMainView('game_ajax');
			else
				$this->view->setMainView('game');

			// Кэшируем настройки профиля в сессию
			if (!$this->session->has('config') || strlen($this->session->get('config')) < 10)
			{
				$inf = $this->db->query("SELECT planet_sort, planet_sort_order, color, timezone, spy FROM game_users_info WHERE id = " . $this->user->getId())->fetch();
				$this->session->set('config', json_encode($inf));
			}

			if (!(int) $this->config->view->get('showPlanetListSelect', 0))
				$this->config->view->offsetSet('showPlanetListSelect', $this->user->getUserOption('planetlistselect'));

			if ($this->request->getQuery('fullscreen') == 'Y')
			{
				$this->cookies->set($this->config->cookie->prefix."_full", "Y", (time() + 30 * 86400), "/", null, $_SERVER["SERVER_NAME"], 0);
				$_COOKIE[$this->config->cookie->prefix."_full"] = 'Y';
			}

			if ($this->request->getServer('SERVER_NAME') == 'vk.xnova.su')
			{
				$this->config->view->offsetSet('socialIframeView', 2);
				$this->config->app->offsetSet('ajaxNavigation', 2);
			}

			if ($this->cookies->has($this->config->cookie->prefix."_full") && $this->cookies->get($this->config->cookie->prefix."_full") == 'Y')
			{
				$this->config->view->offsetSet('socialIframeView', 0);
				$this->config->view->offsetSet('showPlanetListSelect', 0);
			}

			switch ((int) $this->config->app->get('ajaxNavigation', 0))
			{
				case 0:
					$this->view->setVar('ajaxNavigation', 0);
					break;
				case 1:
					$this->view->setVar('ajaxNavigation', $this->user->getUserOption('ajax_navigation'));
					break;
				default:
					$this->view->setVar('ajaxNavigation', 1);
			}

			// Заносим настройки профиля в основной массив
			$inf = json_decode($this->session->get('config'), true);

			foreach ($inf as $key => $value)
				$this->user->{$key} = $value;

			$this->view->setVar('isPopup', ($this->request->has('popup') ? 1 : 0));
			$this->view->setVar('timezone', (isset($inf['timezone']) ? intval($inf['timezone']) : 0));
			$this->view->setVar('userId', $this->user->getId());
			$this->view->setVar('adminlevel', $this->user->authlevel);

			if ($this->request->has('popup'))
				$this->game->addRequestData('popup', true);

			$options = [
				'route' => [
					'controller' => $this->dispatcher->getControllerName(),
					'action' => $this->dispatcher->getActionName(),
				],
				'menu' => [],
				'planet' => false
			];

			foreach (_getText('main_menu') as $code => $data)
			{
				if ($data[2] > $this->user->authlevel)
					continue;

				$options['menu'][] = [
					'id' => $code,
					'url' => $this->url->get($data[1]),
					'text' => trim($data[0]),
					'new' => isset($data[3])
				];
			}

			$this->view->setVar('options', $options);

			$this->user->getAllyInfo();

			User::checkLevel($this->user);

			// Выставляем планету выбранную игроком из списка планет
			$this->user->setSelectedPlanet();

			$controller = $this->dispatcher->getControllerName();

			if (($this->user->race == 0 || $this->user->avatar == 0) && $controller != 'infos' && $controller != 'content' && $controller != 'start' && $controller != 'error')
				$this->dispatcher->forward(['controller' => 'start', 'action' => 'index']);
			elseif ($controller == 'index')
				$this->dispatcher->forward(['controller' => 'overview', 'action' => 'index']);
		}
		else
		{
			$this->showTopPanel(false);
			$this->showLeftPanel(false);

			if ($this->getDI()->has('game'))
				$this->game->checkReferLink();
		}

		return true;
	}

	public function afterExecuteRoute ()
	{
		if ($this->view->isDisabled())
			return true;

		$this->view->setVar('controller', $this->dispatcher->getControllerName().($this->dispatcher->getControllerName() == 'buildings' ? $this->dispatcher->getActionName() : ''));

		if (!$this->request->isAjax() && isset($this->game->getRequestData()['redirect']))
			return $this->response->redirect($this->game->getRequestData()['redirect']);

		if ($this->auth->isAuthorized())
		{
			$this->view->setVar('deleteUserTimer', $this->user->deltime);
			$this->view->setVar('vocationTimer', $this->user->vacation);
			$this->view->setVar('messages', $this->user->messages);
			$this->view->setVar('messages_ally', $this->user->messages_ally);
			$this->view->setVar('tutorial', $this->user->tutorial);

			$parse = [];

			if ($this->getDI()->has('planet'))
				$parse = $this->ShowTopNavigationBar();
			else
				$this->showTopPanel(false);

			$parse['tutorial'] = $this->user->tutorial;

			$planetsList = $this->cache->get('app::planetlist_'.$this->user->getId());

			if ($planetsList === null)
			{
				$planetsList = User::getPlanets($this->user->getId());

				if (count($planetsList))
					$this->cache->save('app::planetlist_'.$this->user->getId(), $planetsList, 600);
			}

			$parse['list'] = $planetsList;
			$parse['current'] = $this->user->planet_current;

			$this->view->setVar('planet', $parse);
		}
		else
			$this->showTopPanel(false);

		$this->view->setVar('topPanel', $this->showTopPanel);
		$this->view->setVar('leftMenu', $this->showLeftMenu);

		$this->game->addRequestData('title', str_replace("\n", "", $this->tag->getTitle(false)));

		$this->tag->appendTitle(Options::get('site_title'));

		$this->game->addRequestData('title_full', str_replace("\n", "", $this->tag->getTitle(false)));
		$this->game->addRequestData('url', $this->router->getRewriteUri());

		return true;
	}

	public function ShowTopNavigationBar ()
	{
		$parse = [];

		$parse['image'] = $this->planet->image;
		$parse['name'] = $this->planet->name;
		$parse['time'] = time();

		$parse['planetlist'] = '';

		if ($this->config->view->get('showPlanetListSelect', 0))
		{
			$planetsList = $this->cache->get('app::planetlist_'.$this->user->getId().'');

			if ($planetsList === NULL)
			{
				$planetsList = User::getPlanets($this->user->getId());

				$this->cache->save('app::planetlist_'.$this->user->getId().'', $planetsList, 300);
			}

			foreach ($planetsList AS $CurPlanet)
			{
				if ($CurPlanet['destruyed'] > 0)
					continue;

				$parse['planetlist'] .= "\n<option ";

				if ($CurPlanet['planet_type'] == 3)
					$parse['planetlist'] .= "style=\"color:red;\" ";
				elseif ($CurPlanet['planet_type'] == 5)
					$parse['planetlist'] .= "style=\"color:yellow;\" ";

				if ($CurPlanet['id'] == $this->user->planet_current)
					$parse['planetlist'] .= "selected=\"selected\" ";

				$parse['planetlist'] .= "value=\"/" . $this->dispatcher->getControllerName() . "/";

				if ($this->dispatcher->getActionName() != 'index')
					$parse['planetlist'] .= "" . $this->dispatcher->getActionName().'/';

				$parse['planetlist'] .= "?chpl=" . $CurPlanet['id'] . "\">";

				$parse['planetlist'] .= "" . $CurPlanet['name'];
				$parse['planetlist'] .= "&nbsp;[" . $CurPlanet['galaxy'] . ":" . $CurPlanet['system'] . ":" . $CurPlanet['planet'];
				$parse['planetlist'] .= "]&nbsp;&nbsp;</option>";
			}
		}

		foreach (Vars::getResources() AS $res)
		{
			$parse[$res] = [
				'title' => _getText('res', $res),
				'url' => $this->url->get('info/'._getText('res_builds', $res).'/'),
				'current' => floor(floatval($this->planet->{$res})),
				'max' => $this->planet->{$res.'_max'},
				'production' => 0,
				'power' => $this->planet->getBuild($res.'_mine')['power'] * 10
			];

			if ($this->user->vacation <= 0)
				$parse[$res]['production'] = $this->planet->{$res.'_perhour'} + floor($this->config->game->get($res.'_basic_income', 0) * $this->config->game->get('resource_multiplier', 1));
		}

		$parse['energy'] = [
			'current' => $this->planet->energy_max + $this->planet->energy_used,
			'max' => $this->planet->energy_max
		];

		$parse['battery'] = [
			'current' => round($this->planet->energy_ak),
			'max' => $this->planet->battery_max,
			'power' => 0,
			'tooltip' => ''
		];

		$parse['credits'] = $this->user->credits;

		$parse['officiers'] = [];

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_OFFICIER) AS $officier)
			$parse['officiers'][$officier] = $this->user->{Vars::getName($officier)};

		$parse['battery']['power'] = ($this->planet->battery_max > 0 ? round($this->planet->energy_ak / $this->planet->battery_max, 2) * 100 : 0);
		$parse['battery']['power'] = min(100, max(0, $parse['battery']['power']));

		if ($parse['battery']['power'] > 0 && $parse['battery']['power'] < 100)
		{
			if (($this->planet->energy_max + $this->planet->energy_used) > 0)
				$parse['battery']['tooltip'] .= '<br>Заряд: ' . Format::time(round(((round(250 * $this->planet->getBuild('solar_plant')['level']) - $this->planet->energy_ak) / ($this->planet->energy_max + $this->planet->energy_used)) * 3600)) . '';
			elseif (($this->planet->energy_max + $this->planet->energy_used) < 0)
				$parse['battery']['tooltip'] .= '<br>Разряд: ' . Format::time(round(($this->planet->energy_ak / abs($this->planet->energy_max + $this->planet->energy_used)) * 3600)) . '';
		}

		$parse['messages'] = $this->user->messages;

		if ($this->user->messages_ally > 0 && $this->user->ally_id == 0)
		{
			$this->user->messages_ally = 0;
			$this->db->updateAsDict('game_users', ['messages_ally' => 0], "id = ".$this->user->id);
		}

		$parse['ally_messages'] = ($this->user->ally_id != 0) ? $this->user->messages_ally : '';

		return $parse;
	}

	public function showTopPanel ($view = true)
	{
		$this->showTopPanel = $view;
	}

	public function showLeftPanel ($view = true)
	{
		$this->showLeftMenu = $view;
	}
}