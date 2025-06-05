<?php

namespace App\Http\Controllers;

use App\Facades\Vars;
use App\Exceptions\Exception;
use App\Models\Fleet;
use App\Models\LogCredit;
use Illuminate\Http\Request;

class RaceController extends Controller
{
	public function index()
	{
		$isChangeAvailable = $this->user->race_change_count > 0 || $this->user->credits >= 100;

		return [
			'change' => $this->user->race_change_count,
			'change_available' => $isChangeAvailable,
		];
	}

	public function change(Request $request)
	{
		if (!$this->user->race) {
			throw new Exception('Нельзя изменить фракцию в данный момент');
		}

		$isChangeAvailable = $this->user->race_change_count > 0 || $this->user->credits >= 100;

		if (!$isChangeAvailable) {
			throw new Exception('Вы исчерпали лимит на смену фракции');
		}

		$r = $request->post('race', 0);
		$r = max(min($r, 4), 0);

		if (!$r) {
			throw new Exception('Выберите фракцию');
		}

		$queueCount = $this->user->queue_count;

		$flyingFleets = Fleet::query()->whereBelongsTo($this->user)->count();

		if ($queueCount > 0) {
			throw new Exception('Для смены фракции y вac нe дoлжнo идти cтpoитeльcтвo или иccлeдoвaниe нa плaнeтe');
		} elseif ($flyingFleets > 0) {
			throw new Exception('Для смены фракции y вac нe дoлжeн нaxoдитьcя флoт в пoлeтe');
		}

		$this->user->race = $r;

		if ($this->user->race_change_count > 0) {
			$this->user->race_change_count--;
		} else {
			$this->user->credits -= 100;

			LogCredit::create([
				'user_id' => $this->user->id,
				'amount' => 100,
				'type' => 7,
			]);
		}

		$this->user->update();

		foreach ($this->user->planets as $planet) {
			$planet->updateAmount(Vars::getIdByName('corvete'), 0);
			$planet->updateAmount(Vars::getIdByName('interceptor'), 0);
			$planet->updateAmount(Vars::getIdByName('dreadnought'), 0);
			$planet->updateAmount(Vars::getIdByName('corsair'), 0);

			$planet->update();
		}
	}
}
