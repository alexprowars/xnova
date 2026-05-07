<?php

namespace App\Http\Controllers;

use App\Engine\Game;
use App\Exceptions\Exception;
use App\Services\FleetService;
use Inertia\Inertia;
use Inertia\Response;

class OverviewController extends Controller
{
	public function index(): Response
	{
		$user = auth()->user();

		$dailyBonus = null;

		if ($user->daily_bonus->isPast()) {
			$bonusFactor = min(50, $user->daily_bonus_factor + 1);

			if ($user->daily_bonus->subDay()->isPast()) {
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

	public function daily()
	{
		if ($this->user->daily_bonus?->isFuture()) {
			throw new Exception('Вы не можете получить ежедневный бонус в данное время');
		}

		$factor = $this->user->daily_bonus_factor < 50
			? $this->user->daily_bonus_factor + 1 : 50;

		if (!$this->user->daily_bonus || $this->user->daily_bonus->subDay()->isPast()) {
			$factor = 1;
		}

		$add = $factor * 500 * Game::getSpeed('mine');

		$this->planet->metal += $add;
		$this->planet->crystal += $add;
		$this->planet->deuterium += $add;
		$this->planet->update();

		$this->user->daily_bonus = now()->addSeconds(86400);
		$this->user->daily_bonus_factor = $factor;

		if ($this->user->daily_bonus_factor > 1) {
			$this->user->credits++;
		}

		$this->user->update();

		if ($this->user->daily_bonus_factor > 1) {
			Inertia::flash('toast', 'Спасибо за поддержку!<br>Вы получили в качестве бонуса по <b>' . $add . '</b> Металла, Кристаллов и Дейтерия, а также 1 кредит.');
		} else {
			Inertia::flash('toast', 'Спасибо за поддержку!<br>Вы получили в качестве бонуса по <b>' . $add . '</b> Металла, Кристаллов и Дейтерия.');
		}
	}
}
