<?php

namespace App\Http\Controllers;

use App\Engine\Game;
use App\Services\FleetService;
use Inertia\Inertia;
use Inertia\Response;

class OverviewController extends Controller
{
	public function index(): Response
	{
		$user = auth()->user();

		$dailyBonus = null;

		if (!$user->daily_bonus?->isFuture()) {
			$bonusFactor = min(50, $user->daily_bonus_factor + 1);

			if (!$user->daily_bonus || $user->daily_bonus->addDay()->isPast()) {
				$bonusFactor = 1;
			}

			$dailyBonus = $bonusFactor * 500 * Game::getSpeed('mine');
		}

		return Inertia::render('Overview', [
			'dailyBonus' => $dailyBonus,
			'fleets' => FleetService::list(auth()->user()),
		]);
	}

	public function rename()
	{
		return Inertia::render('RenamePlanet');
	}
}
