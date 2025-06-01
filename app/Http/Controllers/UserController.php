<?php

namespace App\Http\Controllers;

use App\Engine\Game;
use App\Exceptions\Exception;

class UserController extends Controller
{
	public function setPlanet(int $planetId)
	{
		$this->user->setSelectedPlanet($planetId);
	}

	public function dailyBonus()
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

		return [
			'resources' => $add,
			'credits' => $this->user->daily_bonus_factor > 1,
		];
	}
}
