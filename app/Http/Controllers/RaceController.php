<?php

namespace App\Http\Controllers;

use App\Exceptions\Exception;
use App\Models\Fleet;
use App\Models\LogsCredit;
use App\Support\ToastType;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RaceController extends Controller
{
	public function index()
	{
		$isChangeAvailable = ($this->user->race_change_count > 0 || $this->user->credits >= 100)
			&& !$this->user->isVacation();

		return Inertia::render('Race', [
			'change' => $this->user->race_change_count,
			'change_available' => $isChangeAvailable,
		]);
	}

	public function change(Request $request)
	{
		if (!$this->user->race) {
			throw new Exception(__('main.race_change_unavailable'));
		}

		$isChangeAvailable = $this->user->race_change_count > 0 || $this->user->credits >= 100;

		if (!$isChangeAvailable) {
			throw new Exception(__('main.race_change_limit_reached'));
		}

		$r = $request->post('race', 0);
		$r = max(min($r, 4), 0);

		if (!$r) {
			throw new Exception(__('main.race_select_required'));
		}

		if ($r == $this->user->race) {
			throw new Exception(__('main.race_already_selected'));
		}

		$queueCount = $this->user->queue()->count();

		$flyingFleets = Fleet::query()->whereBelongsTo($this->user)->count();

		if ($queueCount > 0) {
			throw new Exception(__('main.race_queue_not_empty'));
		} elseif ($flyingFleets > 0) {
			throw new Exception(__('main.race_fleet_in_flight'));
		}

		$raceChangedAt = CarbonImmutable::now();
		$planets = $this->user->planets()->get();

		foreach ($planets as $planet) {
			$planet->setRelation('user', $this->user);
			$planet->getProduction($raceChangedAt)->update();
		}

		$this->user->race = $r;

		if ($this->user->race_change_count > 0) {
			$this->user->race_change_count--;
		} else {
			$this->user->credits -= 100;

			LogsCredit::create([
				'user_id' => $this->user->id,
				'amount' => -100,
				'type' => 7,
			]);
		}

		$this->user->update();

		foreach ($planets as $planet) {
			$planet->updateAmount('corvete', 0);
			$planet->updateAmount('interceptor', 0);
			$planet->updateAmount('dreadnought', 0);
			$planet->updateAmount('corsair', 0);

			$planet->update();
		}

		toast(ToastType::SUCCESS, __('main.race_changed'));

		return to_route('overview');
	}
}
