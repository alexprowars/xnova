<?php

namespace Xnova;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Xnova\Exceptions\RedirectException;

class Controller extends BaseController
{
	private $views = [
		'header' => true,
		'footer' => true,
		'planets' => true,
		'menu' => true,
		'resources' => true,
		'chat' => true
	];

	private $title = '';
	/** @var User */
	protected $user = null;
	/** @var Planet */
	protected $planet = null;

	private $loadPlanet = true;

	public function __construct ()
	{
		$this->middleware(function ($request, $next)
		{
			$this->init();

			return $next($request);
		});
	}

	private function init ()
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

		$this->setTitle(Config::get('game.site_title', ''));

		Vars::init();

		if (Auth::check())
		{
			$this->user = Auth::user();

			// Кэшируем настройки профиля в сессию
			if (!Session::has('config') || strlen(Session::get('config')) < 10)
			{
				$inf = DB::table('users_info')->select('settings')->where('id', Auth::id())->first();

				Session::put('config', $inf->settings);
			}

			if (!(int) Config::get('game.view.showPlanetListSelect', 0))
				Config::set('game.view.showPlanetListSelect', $this->user->getUserOption('planetlistselect'));

			if (Request::input('fullscreen') == 'Y')
			{
				Cookie::queue("full", "Y", (time() + 30 * 86400), "/", null, $_SERVER["SERVER_NAME"], 0);
				$_COOKIE["full"] = 'Y';
			}

			if (Request::server('SERVER_NAME') == 'vk.xnova.su')
				Config::set('game.view.socialIframeView', 1);

			if (Cookie::has("full") && Cookie::get("full") == 'Y')
			{
				Config::set('game.view.socialIframeView', 0);
				Config::set('game.view.showPlanetListSelect', 0);
			}

			// Заносим настройки профиля в основной массив
			$inf = json_decode(Session::get('config'), true);

			if (is_array($inf))
				$this->user->setOptions($inf);

			if (!$this->user->getUserOption('chatbox'))
				$this->views['chat'] = false;

			$this->user->getAllyInfo();
			$this->user->checkLevel();

			// Выставляем планету выбранную игроком из списка планет
			$this->user->setSelectedPlanet();

			$controller = Route::current()->getName();

			if (($this->user->race == 0 || $this->user->avatar == 0) && !in_array($controller, ['infos', 'content', 'start', 'error', 'logout']))
				throw new RedirectException('', '/start/');

			if (Request::has('initial') || $this->loadPlanet)
				$this->planet = $this->user->getCurrentPlanet();
		}
		else
		{
			$this->showTopPanel(false);
			$this->showLeftPanel(false);

			Game::checkReferLink();
		}
	}

	public function setViews ($view, $mode)
	{
		if (isset($this->views[$view]))
			$this->views[$view] = (bool) $mode;
	}

	public function getViews () : array
	{
		return $this->views;
	}

	public function showTopPanel ($view = true)
	{
		$this->setViews('resources', $view);
	}

	public function showLeftPanel ($view = true)
	{
		$this->setViews('header', $view);
		$this->setViews('footer', $view);
		$this->setViews('menu', $view);
		$this->setViews('planets', $view);
	}

	public function setTitle (string $title = '')
	{
		$this->title = $title;
	}

	public function getTitle (): string
	{
		return $this->title;
	}
}
