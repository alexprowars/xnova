<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use App\Exceptions\RedirectException;
use App\Models\UserDetail;
use App\Models\Fleet;
use App\Planet;
use App\Queue;
use App\Controller;
use App\Vars;

class RaceController extends Controller
{
	public function change()
	{
		$numChanges = (int) DB::selectOne('SELECT free_race_change FROM user_details WHERE id = ' . $this->user->id)->free_race_change;

		$isChangeAvailable = ($numChanges > 0) || ($this->user->credits >= 100);

		if ($this->user->race != 0 && $isChangeAvailable) {
			$r = Request::post('race', 0);
			$r = max(min($r, 4), 1);

			if ($r > 0) {
				$queueManager = new Queue($this->user);
				$queueCount = $queueManager->getCount();

				$UserFlyingFleets = Fleet::query()->where('owner', $this->user->id)->count();

				if ($queueCount > 0) {
					throw new RedirectException('Для смены фракции y вac нe дoлжнo идти cтpoитeльcтвo или иccлeдoвaниe нa плaнeтe.', "/race/");
				} elseif ($UserFlyingFleets > 0) {
					throw new RedirectException('Для смены фракции y вac нe дoлжeн нaxoдитьcя флoт в пoлeтe.', "/race/");
				} else {
					$this->user->race = $r;

					if ($numChanges > 0) {
						UserDetail::query()->where('id', $this->user->id)->decrement('free_race_change', 1);
					} else {
						$this->user->credits -= 100;

						UserDetail::query()->insert([
							'uid' => $this->user->id,
							'time' => time(),
							'credits' => 100,
							'type' => 7,
						]);
					}

					$this->user->update();

					$planets = Planet::query()
						->where('id_owner', $this->user->id)
						->get();

					foreach ($planets as $planet) {
						$planet->updateAmount(Vars::getIdByName('corvete'), 0);
						$planet->updateAmount(Vars::getIdByName('interceptor'), 0);
						$planet->updateAmount(Vars::getIdByName('dreadnought'), 0);
						$planet->updateAmount(Vars::getIdByName('corsair'), 0);

						$planet->update();
					}

					throw new RedirectException('Фракция изменена', '/overview/');
				}
			}
		}
	}

	public function index()
	{
		$numChanges = (int) DB::selectOne('SELECT free_race_change FROM user_details WHERE id = ' . $this->user->id)->free_race_change;

		if (Request::has('sel') && $this->user->race == 0) {
			$r = Request::input('sel', 0);
			$r = max(min($r, 4), 1);

			if ($r > 0) {
				$update = ['race' => intval($r), 'bonus' => time() + 86400];

				foreach (Vars::getItemsByType(Vars::ITEM_TYPE_OFFICIER) as $oId) {
					$update[Vars::getName($oId)] = time() + 86400;
				}

				$this->user->update($update);

				throw new RedirectException('', 'tutorial/');
			}
		}

		$isChangeAvailable = ($numChanges > 0) || ($this->user->credits >= 100);

		$this->showTopPanel(false);
		$this->showLeftPanel(!($this->user->race == 0));

		return [
			'change' => (int) $numChanges,
			'change_available' => $isChangeAvailable,
		];
	}
}
