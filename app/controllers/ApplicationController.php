<?php
namespace App\Controllers;

use App\Lang;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\View;
use Phalcon\Tag;

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
 * @property \App\Auth\Auth auth
 * @property \Phalcon\Mvc\Dispatcher dispatcher
 * @property \Phalcon\Flash\Direct flash
 * @property \Phalcon\Config|\stdClass config
 * @property \App\Game game
 */
class ApplicationController extends Controller
{
	public $private = 0;

	private $showTopPanel = true;
	private $showLeftMenu = true;

	public function initialize()
	{
		if (function_exists('sys_getloadavg'))
		{
			$load = sys_getloadavg();

			if ($load[0] > 15)
			{
				header('HTTP/1.1 503 Too busy, try again later');
				die('Server too busy. Please try again later.');
			}
		}

		Lang::setLang($this->config->app->language);

		if ($this->request->isAjax())
			$this->view->disableLevel(View::LEVEL_MAIN_LAYOUT);
		else
		{
			$this->tag->setTitleSeparator(' | ');
			$this->tag->setTitle($this->config->app->name);
	        $this->tag->setDoctype(Tag::HTML5);
		}

		if ($this->auth->isAuthorized())
		{
			$this->view->setMainView('game');

			$assets = $this->assets->collection('headerCss');

			$assets->addCss('/assets/css/bootstrap.css');
			$assets->addCss('/assets/css/formate.css');
			$assets->addCss('/assets/css/style.css');
			$assets->addCss('/assets/css/media.css');
			$assets->addCss('/assets/css/mobile.css');
			$assets->addCss('/assets/css/jquery-ui.css');

			$assets = $this->assets->collection('headerJs');

			$assets->addJs('//yastatic.net/jquery/1.11.1/jquery.min.js');
			$assets->addJs('//yastatic.net/jquery-ui/1.11.2/jquery-ui.min.js');
			$assets->addJs('/assets/js//script.js');
			$assets->addJs('/assets/js/jquery.form.min.js');
			$assets->addJs('/assets/js/game.js');
			$assets->addJs('/assets/js/universe.js');
			$assets->addJs('/assets/js/flotten.js');
			$assets->addJs('/assets/js/smiles.js');
			$assets->addJs('/assets/js/ed.js');
			$assets->addJs('/assets/js/jquery.touchSwipe.min.js');

			// Кэшируем настройки профиля в сессию
			if (!$this->session->has('config') || strlen($this->session->get('config')) < 10)
			{
				$inf = $this->db->query("SELECT planet_sort, planet_sort_order, color, timezone, spy FROM game_users_info WHERE id = " . $this->user->getId())->fetch();
				$this->session->set('config', json_encode($inf));
			}

			if (!$this->config->app->get('showPlanetListSelect', 0))
				$this->config->app->offsetSet('showPlanetListSelect', $this->user->getUserOption('planetlistselect'));

			if ($this->request->getQuery('fullscreen') == 'Y')
			{
				$this->cookies->set($this->config->cookie->prefix."_full", "Y", (time() + 30 * 86400), "/", $_SERVER["SERVER_NAME"], 0);
				$_COOKIE[$this->config->cookie->prefix."_full"] = 'Y';
			}

			if ($this->request->getServer('SERVER_NAME') == 'ok1.xnova.su')
			{
				$this->config->app->offsetSet('socialIframeView', 2);
				$this->config->app->offsetSet('ajaxNavigation', 2);
			}

			if ($this->request->getServer('SERVER_NAME') == 'vk.xnova.su')
			{
				$this->config->app->offsetSet('socialIframeView', 2);
				$this->config->app->offsetSet('ajaxNavigation', 2);
			}

			if ($this->cookies->has($this->config->cookie->prefix."_full") && $this->cookies->get($this->config->cookie->prefix."_full") == 'Y')
			{
				$this->config->app->offsetSet('socialIframeView', 0);
				$this->config->app->offsetSet('overviewListView', 1);
				$this->config->app->offsetSet('showPlanetListSelect', 0);
			}

			$this->view->setVar('timezone', 0);
			$this->view->setVar('topPanel', $this->showTopPanel);
			$this->view->setVar('leftMenu', $this->showLeftMenu);
			$this->view->setVar('adminlevel', $this->user->authlevel);
			$this->view->setVar('controller', $this->dispatcher->getControllerName());

			// Заносим настройки профиля в основной массив
			$inf = json_decode($this->session->get('config'), true);
			//user::get()->data = array_merge(user::get()->data, $inf);
			$this->user->getAllyInfo();

			$this->checkUserLevel();

			if ($this->config->app->code == 'OK1U')
			{
				$points = $this->db->fetchColumn("SELECT `total_points` FROM game_statpoints WHERE `stat_type` = '1' AND `stat_code` = '1' AND `id_owner` = '" . $this->user->getId() . "'");

				if (!$points || $points < 1000)
				{
					$this->config->game->offsetSet('game_speed', $this->config->game->get('game_speed', 1) * 5);
					$this->config->game->offsetSet('resource_multiplier', $this->config->game->get('resource_multiplier', 1) * 3);
					$this->config->game->offsetSet('noob', 1);
				}
			}

			// Выставляем планету выбранную игроком из списка планет
			$this->user->setSelectedPlanet();

			$controller = $this->dispatcher->getControllerName();

			if (($this->user->race == 0 || $this->user->avatar == 0) && $controller != 'infos' && $controller != 'content' && $controller != 'start')
				$this->dispatcher->forward(array('controller' => 'start'));
		}

		return true;
	}

	public function checkUserLevel ()
	{
		if (!is_object($this->user))
			return;

		$indNextXp = pow($this->user->lvl_minier, 3);
		$warNextXp = pow($this->user->lvl_raid, 2);

		$giveCredits = 0;

		if ($this->user->xpminier >= $indNextXp && $this->user->lvl_minier < $this->config->level->get('max_ind', 100))
		{
			$this->user->saveData(array
			(
				'+lvl_minier' 	=> 1,
				'+credits' 		=> $this->config->level->get('credits', 10),
				'-xpminier' 	=> $indNextXp
			));

			$this->game->sendMessage($this->user->getId(), 0, 0, 1, '', '<a href=?set=officier>Получен новый промышленный уровень</a>');

			$this->user->lvl_minier += 1;
			$this->user->xpminier 	-= $indNextXp;

			$giveCredits += $this->config->level->get('credits', 10);
		}

		if ($this->user->xpraid >= $warNextXp && $this->user->lvl_raid < $this->config->level->get('max_war', 100))
		{
			$this->user->saveData(array
			(
				'+lvl_raid' => 1,
				'+credits' 	=> $this->config->level->get('credits', 10),
				'-xpraid' 	=> $warNextXp
			));

			$this->game->sendMessage($this->user->getId(), 0, 0, 1, '', '<a href=?set=officier>Получен новый военный уровень</a>');

			$this->user->lvl_raid 	+= 1;
			$this->user->xpraid 	-= $warNextXp;

			$giveCredits += $this->config->level->get('credits', 10);
		}

		if ($giveCredits != 0)
		{
			$this->db->insertAsDict(
				"game_log_credits",
				array
				(
					'uid' 		=> $this->user->getId(),
					'time' 		=> time(),
					'credits' 	=> $giveCredits,
					'type' 		=> 4,
				)
			);

			$reffer = $this->db->query("SELECT u_id FROM game_refs WHERE r_id = " . $this->user->getId())->fetch();

			if (isset($reffer['u_id']))
			{
				$this->db->query("UPDATE game_users SET credits = credits + " . round($giveCredits / 2) . " WHERE id = " . $reffer['u_id'] . "");
				$this->db->query("INSERT INTO game_log_credits (uid, time, credits, type) VALUES (" . $reffer['u_id'] . ", " . time() . ", " . round($giveCredits / 2) . ", 3)");
			}
		}
	}

	public function showTopPanel ($view = true)
	{
		$this->showTopPanel = $view;
	}

	public function showLeftPanel ($view = true)
	{
		$this->showLeftMenu = $view;
	}

	public function message ($text, $title = '')
	{
		$this->view->pick('shared/message');
		$this->view->setVar('text', $text);
		$this->view->setVar('title', $title);
		$this->view->start();

		return true;
	}
}

?>