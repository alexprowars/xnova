<?php

namespace App\Http\Controllers;

use App\Engine\Fleet;
use App\Models\Planet;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Nubs\RandomNameGenerator;

class Controller extends BaseController
{
	protected ?User $user = null;
	protected ?Planet $planet = null;

	public function __construct()
	{
		$this->middleware(function (Request $request, Closure $next) {
			$controller = $request->route()->getController();

			if ($request->user() && (!$request->user()->race || !$request->user()->sex) && $controller && !in_array($controller::class, [StateController::class, InfoController::class, ContentController::class, StartController::class, LogoutController::class])) {
				return redirect()->away('/start');
			}

			return $next($request);
		});

		$this->middleware(function (Request $request, Closure $next) {
			$this->init($request->user());

			return $next($request);
		});
	}

	private function init(?User $user): void
	{
		if (!$user) {
			return;
		}

		$this->user = $user;

		if (empty($this->user->username)) {
			$this->user->username = RandomNameGenerator\All::create()->getName();
		}

		if (!(int) config('game.showPlanetListSelect', 0)) {
			config(['settings.showPlanetListSelect' => (int) $this->user->getOption('planetlistselect')]);
		}

		$this->user->getAllyInfo();

		$this->planet = $this->user->getCurrentPlanet();

		if ($this->planet) {
			if (!$this->planet->checkOwnerPlanet()) {
				$this->user->planet_current = $this->user->planet_id;
				$this->user->update();

				$this->planet = Planet::find($this->user->planet_id);
			}

			$this->planet->checkUsedFields();

			// Обновляем ресурсы на планете когда это необходимо
			if ($this->planet->last_update->diffInSeconds() < 60) {
				$this->planet->getProduction()->update(true);
			} else {
				$this->planet->getProduction()->update();
			}

			Fleet::setShipsEngine($this->user);
		}
	}
}
