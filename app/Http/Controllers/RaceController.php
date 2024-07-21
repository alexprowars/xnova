<?php

namespace App\Http\Controllers;

use App\Engine\Enums\ItemType;
use App\Engine\QueueManager;
use App\Engine\Vars;
use App\Exceptions\RedirectException;
use App\Models\Fleet;
use App\Models\LogCredit;
use Illuminate\Support\Facades\Request;

class RaceController extends Controller
{
	public function change()
	{
		$isChangeAvailable = $this->user->race_change_count > 0 || $this->user->credits >= 100;

		if ($this->user->race != 0 && $isChangeAvailable) {
			$r = Request::post('race', 0);
			$r = max(min($r, 4), 0);

			if ($r > 0) {
				$queueManager = new QueueManager($this->user);
				$queueCount = $queueManager->getCount();

				$UserFlyingFleets = Fleet::query()->where('user_id', $this->user->id)->count();

				if ($queueCount > 0) {
					throw new RedirectException("/race", 'Для смены фракции y вac нe дoлжнo идти cтpoитeльcтвo или иccлeдoвaниe нa плaнeтe.');
				} elseif ($UserFlyingFleets > 0) {
					throw new RedirectException("/race", 'Для смены фракции y вac нe дoлжeн нaxoдитьcя флoт в пoлeтe.');
				} else {
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

					throw new RedirectException('/overview', 'Фракция изменена');
				}
			}
		}
	}

	public function index()
	{
		if (Request::has('sel') && $this->user->race == 0) {
			$r = Request::input('sel', 0);
			$r = max(min($r, 4), 0);

			if ($r > 0) {
				$update = ['race' => (int) $r, 'bonus' => time() + 86400];

				foreach (Vars::getItemsByType(ItemType::OFFICIER) as $oId) {
					$update[Vars::getName($oId)] = time() + 86400;
				}

				$this->user->update($update);

				throw new RedirectException('/tutorial');
			}
		}

		$isChangeAvailable = $this->user->race_change_count > 0 || $this->user->credits >= 100;

		return response()->state([
			'change' => $this->user->race_change_count,
			'change_available' => $isChangeAvailable,
		]);
	}
}
