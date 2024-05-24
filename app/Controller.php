<?php

namespace App;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use App\Exceptions\RedirectException;
use App\Models\User;
use App\Models\Planet;

class Controller extends BaseController
{
	protected ?User $user = null;
	protected ?Planet $planet = null;

	protected $loadPlanet = false;

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

		if (!(int) config('settings.view.showPlanetListSelect', 0)) {
			config(['settings.view.showPlanetListSelect' => (int) $this->user->getOption('planetlistselect')]);
		}

		$this->user->getAllyInfo();
		$this->user->checkLevel();

		if ($request->has('chpl') && is_numeric($request->input('chpl'))) {
			$this->user->setSelectedPlanet($request->input('chpl'));
		}

		$controller = Route::current()->getName();

		if (($this->user->race == 0 || $this->user->avatar == 0) && !in_array($controller, ['infos', 'content', 'start', 'error', 'logout'])) {
			throw new RedirectException('', '/start/');
		}

		if ($request->has('initial') || $this->loadPlanet) {
			$this->planet = $this->user->getCurrentPlanet(true);
		}

		Fleet::SetShipsEngine($this->user);
	}
}
