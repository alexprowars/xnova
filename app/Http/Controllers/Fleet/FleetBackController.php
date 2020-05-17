<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace Xnova\Http\Controllers\Fleet;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Xnova\Controller;
use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\RedirectException;
use Xnova\Models\Fleet;

class FleetBackController extends Controller
{
	public function index()
	{
		$fleetId = (int) Request::post('id', 0);

		if ($fleetId <= 0) {
			throw new ErrorException('Не выбран флот');
		}

		/** @var Fleet $fleet */
		$fleet = Fleet::query()->find($fleetId);

		if (!$fleet || $fleet->owner != $this->user->id) {
			throw new ErrorException(__('fleet.fl_onlyyours'));
		}

		if (!$fleet->canBack()) {
			throw new ErrorException(__('fleet.fl_notback'));
		}

		if ($fleet->end_stay != 0) {
			if ($fleet->start_time > time()) {
				$CurrentFlyingTime = time() - $fleet->create_time;
			} else {
				$CurrentFlyingTime = $fleet->start_time - $fleet->create_time;
			}
		} else {
			$CurrentFlyingTime = time() - $fleet->create_time;
		}

		$ReturnFlyingTime = $CurrentFlyingTime + time();

		if ($fleet->group_id != 0 && $fleet->mission == 1) {
			DB::table('aks')->delete($fleet->group_id);
			DB::table('aks_user')->where('aks_id', $fleet->group_id)->delete();
		}

		$fleet->update([
			'start_time'	=> time() - 1,
			'end_stay' 		=> 0,
			'end_time' 		=> $ReturnFlyingTime + 1,
			'target_owner' 	=> $this->user->id,
			'group_id' 		=> 0,
			'update_time' 	=> $ReturnFlyingTime + 1,
			'mess' 			=> 1,
		]);

		throw new RedirectException(__('fleet.fl_isback'), '/xnova/');
	}
}
