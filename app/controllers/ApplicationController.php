<?php
namespace App\Controllers;

use App\Helpers;
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
 * @property \App\Models\Planet planet
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

	public function afterExecuteRoute ()
	{
		$this->view->setVar('topPanel', $this->showTopPanel);
		$this->view->setVar('leftMenu', $this->showLeftMenu);
		$this->view->setVar('controller', $this->dispatcher->getControllerName().($this->dispatcher->getControllerName() == 'buildings' ? $this->dispatcher->getActionName() : ''));

		if (!$this->request->isAjax() && isset($this->game->getRequestData()['redirect']))
			$this->response->redirect($this->game->getRequestData()['redirect']);

		if ($this->auth->isAuthorized())
		{
			$this->view->setVar('deleteUserTimer', $this->user->deltime);
			$this->view->setVar('vocationTimer', $this->user->vacation);
			$this->view->setVar('messages', $this->user->messages);
			$this->view->setVar('messages_ally', $this->user->messages_ally);
			$this->view->setVar('tutorial', $this->user->tutorial);

			$parse = array();

			if ($this->getDi()->has('planet'))
				$parse = $this->ShowTopNavigationBar();
			else
				$this->showTopPanel(false);

			$parse['tutorial'] = $this->user->tutorial;

			$planetsList = $this->cache->get('app::planetlist_'.$this->user->getId());

			if ($planetsList === NULL)
			{
				$planetsList = $this->user->getUserPlanets($this->user->getId());

				if (count($planetsList))
					$this->cache->save('app::planetlist_'.$this->user->getId(), $planetsList, 600);
			}

			$parse['list'] = $planetsList;
			$parse['current'] = $this->user->planet_current;

			$this->view->setVar('planet', $parse);
		}

		$this->tag->appendTitle($this->config->app->name);
	}

	public function initialize()
	{
		if ($this->dispatcher->wasForwarded() && $this->dispatcher->getControllerName() !== 'error')
			return true;

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

		if ($this->request->isAjax() && !$this->auth->isAuthorized())
			$this->view->disableLevel(View::LEVEL_MAIN_LAYOUT);
		else
		{
			$this->tag->setTitleSeparator(' :: ');
			$this->tag->setTitle($this->config->app->name);
	        $this->tag->setDoctype(Tag::HTML5);
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

		$css = $this->assets->collection('css');
		$css->addCss('/assets/css/jquery-ui.css');

		$js = $this->assets->collection('js');

		$js->addJs('/assets/js/workers.js');
		$js->addJs('//yastatic.net/jquery/1.11.3/jquery.min.js');
		$js->addJs('//yastatic.net/jquery-ui/1.11.2/jquery-ui.min.js');
		$js->addJs('/assets/js/jquery.form.min.js');
		$js->addJs('/assets/js/game.js');

		if ($this->auth->isAuthorized())
		{
			//if (!$this->user->isAdmin())
			//	die('Нельзя пока вам сюда');

			if (DEBUG)
				$css->addCss('https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css');

			$css->addCss('/assets/css/bootstrap.css');
			$css->addCss('/assets/css/formate.css');
			$css->addCss('/assets/css/style.css');
			$css->addCss('/assets/css/media.css');
			$css->addCss('/assets/css/mobile.css');

			$js->addJs('/assets/js//script.js');
			$js->addJs('/assets/js/universe.js');
			$js->addJs('/assets/js/flotten.js');
			$js->addJs('/assets/js/smiles.js');
			$js->addJs('/assets/js/ed.js');
			$js->addJs('/assets/js/jquery.touchSwipe.min.js');

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

			if (!$this->config->view->get('showPlanetListSelect', 0))
				$this->config->view->offsetSet('showPlanetListSelect', $this->user->getUserOption('planetlistselect'));

			if ($this->request->getQuery('fullscreen') == 'Y')
			{
				$this->cookies->set($this->config->cookie->prefix."_full", "Y", (time() + 30 * 86400), "/", $_SERVER["SERVER_NAME"], 0);
				$_COOKIE[$this->config->cookie->prefix."_full"] = 'Y';
			}

			if ($this->request->getServer('SERVER_NAME') == 'ok1.xnova.su')
			{
				$this->config->view->offsetSet('socialIframeView', 2);
				$this->config->app->offsetSet('ajaxNavigation', 2);
			}

			if ($this->request->getServer('SERVER_NAME') == 'vk.xnova.su')
			{
				$this->config->view->offsetSet('socialIframeView', 2);
				$this->config->app->offsetSet('ajaxNavigation', 2);
			}

			if ($this->cookies->has($this->config->cookie->prefix."_full") && $this->cookies->get($this->config->cookie->prefix."_full") == 'Y')
			{
				$this->config->view->offsetSet('socialIframeView', 0);
				$this->config->view->offsetSet('overviewListView', 1);
				$this->config->view->offsetSet('showPlanetListSelect', 0);
			}

			switch ($this->config->app->get('ajaxNavigation', 0))
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

			$this->view->setVar('isPopup', ($this->request->has('popup') ? 1 : 0));
			$this->view->setVar('timezone', 0);
			$this->view->setVar('userId', $this->user->getId());
			$this->view->setVar('adminlevel', $this->user->authlevel);

			$this->game->loadGameVariables();

			// Заносим настройки профиля в основной массив
			$inf = json_decode($this->session->get('config'), true);

			foreach ($inf as $key => $value)
				$this->user->{$key} = $value;

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

			if (($this->user->race == 0 || $this->user->avatar == 0) && $controller != 'infos' && $controller != 'content' && $controller != 'start' && $controller != 'error')
				$this->dispatcher->forward(array('controller' => 'start'));

			if ($controller == 'index')
				$this->dispatcher->forward(array('controller' => 'overview'));
		}
		else
		{
			$this->showTopPanel(false);
			$this->showLeftPanel(false);
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

			$this->game->sendMessage($this->user->getId(), 0, 0, 1, '', '<a href="/fficier/">Получен новый промышленный уровень</a>');

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

			$this->game->sendMessage($this->user->getId(), 0, 0, 1, '', '<a href="/officier/">Получен новый военный уровень</a>');

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

	public function ShowTopNavigationBar ()
	{
		$parse = array();

		$parse['image'] = $this->planet->image;
		$parse['name'] = $this->planet->name;
		$parse['time'] = time();

		$parse['planetlist'] = '';

		if ($this->config->view->get('showPlanetListSelect', 0))
		{
			$planetsList = $this->cache->get('app::planetlist_'.$this->user->getId().'');

			if ($planetsList === NULL)
			{
				$planetsList = $this->user->getUserPlanets($this->user->getId());

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

				if (isset($_GET['set']))
					$parse['planetlist'] .= "value=\"/" . $_GET['set'] . "/";
				else
					$parse['planetlist'] .= "value=\"/overview/";

				if (isset($_GET['mode']))
					$parse['planetlist'] .= "&amp;mode=" . $_GET['mode'];

				$parse['planetlist'] .= "&amp;cp=" . $CurPlanet['id'] . "&amp;re=0\">";

				$parse['planetlist'] .= "" . $CurPlanet['name'];
				$parse['planetlist'] .= "&nbsp;[" . $CurPlanet['galaxy'] . ":" . $CurPlanet['system'] . ":" . $CurPlanet['planet'];
				$parse['planetlist'] .= "]&nbsp;&nbsp;</option>";
			}
		}

		foreach ($this->game->reslist['res'] AS $res)
		{
			$parse[$res] = floor(floatval($this->planet->{$res}));

			$parse[$res.'_m'] = $this->planet->{$res.'_max'};

			if ($this->planet->{$res.'_max'} <= $this->planet->{$res})
				$parse[$res.'_max'] = '<font class="full">';
			else
				$parse[$res.'_max'] = '<font color="#00ff00">';

			$parse[$res.'_max'] .= Helpers::pretty_number($this->planet->{$res.'_max'}) . "</font>";
			$parse[$res.'_ph'] 	= $this->planet->{$res.'_perhour'} + floor($this->config->game->get($res.'_basic_income', 0) * $this->config->game->get('resource_multiplier', 1));
			$parse[$res.'_mp'] 	= $this->planet->{$res.'_mine_porcent'} * 10;
		}

		$parse['energy_max'] 	= Helpers::pretty_number($this->planet->energy_max);
		$parse['energy_total'] 	= Helpers::colorNumber(Helpers::pretty_number($this->planet->energy_max + $this->planet->energy_used));

		$parse['credits'] = Helpers::pretty_number($this->user->credits);

		$parse['officiers'] = array();

		foreach ($this->game->reslist['officier'] AS $officier)
		{
			$parse['officiers'][$officier] = $this->user->{$this->game->resource[$officier]};
		}

		$parse['energy_ak'] = ($this->planet->battery_max > 0 ? round($this->planet->energy_ak / $this->planet->battery_max, 2) * 100 : 0);
		$parse['energy_ak'] = min(100, max(0, $parse['energy_ak']));

		$parse['ak'] = round($this->planet->energy_ak) . " / " . $this->planet->battery_max;

		if ($parse['energy_ak'] > 0 && $parse['energy_ak'] < 100)
		{
			if (($this->planet->energy_max + $this->planet->energy_used) > 0)
				$parse['ak'] .= '<br>Заряд: ' . Helpers::pretty_time(round(((round(250 * $this->planet->{$this->game->resource[4]}) - $this->planet->energy_ak) / ($this->planet->energy_max + $this->planet->energy_used)) * 3600)) . '';
			elseif (($this->planet->energy_max + $this->planet->energy_used) < 0)
				$parse['ak'] .= '<br>Разряд: ' . Helpers::pretty_time(round(($this->planet->energy_ak / abs($this->planet->energy_max + $this->planet->energy_used)) * 3600)) . '';
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

	public function message ($text, $title = '', $redirect = '', $timeout = 5, $left = true)
	{
		$this->view->pick('shared/message');
		$this->view->setVar('text', $text);
		$this->view->setVar('title', $title);
		$this->view->setVar('destination', $redirect);
		$this->view->setVar('time', $timeout);

		$this->tag->setTitle(($title ? strip_tags($title) : 'Сообщение'));
		$this->showTopPanel(false);
		$this->showLeftPanel($left);

		$this->afterExecuteRoute();

		$this->view->start();

		$this->view->render(
			$this->dispatcher->getControllerName(),
			$this->dispatcher->getActionName(),
			$this->dispatcher->getParams()
		);

		$this->view->finish();

		if ($this->request->isAjax())
		{
			$this->response->setJsonContent(
			[
				'status' 	=> $this->game->getRequestStatus(),
				'message' 	=> $this->game->getRequestMessage(),
				'html' 		=> str_replace("\t", ' ', $this->view->getContent()),
				'data' 		=> $this->game->getRequestData()
			]);
			$this->response->setContentType('text/json', 'utf8');
			$this->response->send();
		}
		else
	   		echo $this->view->getContent();

		die();
	}
}

?>