<?php

namespace App\Http\Controllers;

use App\Engine\Fleet;
use App\Engine\Game;
use App\Engine\Vars;
use App\Exceptions\RedirectException;
use App\Models\Planet;
use App\Models\User;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
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

		if (!(int) config('game.view.showPlanetListSelect', 0)) {
			config(['settings.view.showPlanetListSelect' => (int) $this->user->getOption('planetlistselect')]);
		}

		$this->user->getAllyInfo();
		$this->user->checkLevel();

		if ($request->has('chpl') && is_numeric($request->input('chpl'))) {
			$this->user->setSelectedPlanet($request->input('chpl'));
		}

		if ((!$this->user->race || !$this->user->avatar) && !in_array(Route::current()->controller::class, [StateController::class, InfoController::class, ContentController::class, StartController::class, LogoutController::class])) {
			throw new RedirectException('/start');
		}

		$this->planet = $this->user->getCurrentPlanet();

		if ($this->planet) {
			if (!$this->planet->checkOwnerPlanet()) {
				$this->user->planet_current = $this->user->planet_id;
				$this->user->update();

				$this->planet = Planet::find($this->user->planet_id);
			}

			$this->planet->checkUsedFields();

			// Обновляем ресурсы на планете когда это необходимо
			if ($this->planet->last_update?->diffInSeconds() < 60) {
				$this->planet->getProduction()->update(true);
			} else {
				$this->planet->getProduction()->update();
			}

			Fleet::setShipsEngine($this->user);
		}
	}
}
