<?php

namespace App\Http\Controllers;

use App\Engine\Game;
use App\Exceptions\Exception;
use App\Format;
use App\Support\ToastType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
	public function daily()
	{
		$add = DB::transaction(function () {
			$this->user->refreshForUpdate();
			$this->planet->refreshForUpdate();

			if ($this->user->daily_bonus?->isFuture()) {
				throw new Exception(__('main.user_daily_bonus_unavailable'));
			}

			$factor = $this->user->daily_bonus_factor < 50
				? $this->user->daily_bonus_factor + 1 : 50;

			if (!$this->user->daily_bonus || $this->user->daily_bonus->addDay()->isPast()) {
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

			return $add;
		});

		if ($this->user->daily_bonus_factor > 1) {
			toast(ToastType::SUCCESS, __('main.user_daily_bonus_with_credit', ['amount' => Format::number($add)]));
		} else {
			toast(ToastType::SUCCESS, __('main.user_daily_bonus', ['amount' => Format::number($add)]));
		}
	}

	public function setPlanet(Request $request): void
	{
		$planetId = $request->integer('id');

		if (!$planetId) {
			throw new Exception('planet_id undefined');
		}

		if (!$this->user->setSelectedPlanet($planetId)) {
			throw new Exception(__('main.user_planet_unavailable'));
		}
	}
}
