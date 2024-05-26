<?php

namespace App;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use App\Exceptions\RedirectException;
use App\Models\User;
use App\Models\Planet;
use Nubs\RandomNameGenerator;

class Controller extends BaseController
{
	protected ?User $user = null;
	protected ?Planet $planet = null;

	public function __construct()
	{
		$this->middleware(function ($request, $next) {
			$this->init();

			return $next($request);
		});
	}

	private function init()
	{
		$request = Request::instance();

		Vars::init();

		if (!Auth::check()) {
			Game::checkReferLink();
			return;
		}

		$this->user = Auth::user();

		if (empty($this->user->username)) {
			$this->user->username = RandomNameGenerator\All::create()->getName();
		}

		if (!(int) config('settings.view.showPlanetListSelect', 0)) {
			config(['settings.view.showPlanetListSelect' => (int) $this->user->getOption('planetlistselect')]);
		}

		$this->user->getAllyInfo();
		$this->user->checkLevel();

		if ($request->has('chpl') && is_numeric($request->input('chpl'))) {
			$this->user->setSelectedPlanet($request->input('chpl'));
		}

		$controller = Route::current()->getName();

		if ((!$this->user->race || !$this->user->avatar) && !in_array($controller, ['state', 'infos', 'content', 'start', 'error', 'logout'])) {
			throw new RedirectException('', '/start');
		}

		$this->planet = $this->user->getCurrentPlanet(true);

		Fleet::SetShipsEngine($this->user);
	}
}
