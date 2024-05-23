<?php

namespace App\Http\Controllers\Fleet;

use Illuminate\Http\Request;
use App\Controller;
use App\Exceptions\ErrorException;
use App\Exceptions\RedirectException;
use App\Models\Assault;
use App\Models\AssaultUser;
use App\Models\Fleet;

class FleetBackController extends Controller
{
	public function index(Request $request)
	{
		$fleetId = (int) $request->post('id', 0);

		if ($fleetId <= 0) {
			throw new ErrorException('Не выбран флот');
		}

		$fleet = Fleet::query()->find($fleetId);

		if (!$fleet || $fleet->user_id != $this->user->id) {
			throw new ErrorException(__('fleet.fl_onlyyours'));
		}

		if (!$fleet->canBack()) {
			throw new ErrorException(__('fleet.fl_notback'));
		}

		if ($fleet->end_stay != 0) {
			if ($fleet->start_time->isFuture()) {
				$CurrentFlyingTime = time() - $fleet->created_at->getTimestamp();
			} else {
				$CurrentFlyingTime = $fleet->start_time->getTimestamp() - $fleet->created_at->getTimestamp();
			}
		} else {
			$CurrentFlyingTime = time() - $fleet->created_at->getTimestamp();
		}

		$ReturnFlyingTime = $CurrentFlyingTime + time();

		if ($fleet->mission == 1 && $fleet->assault) {
			$fleet->assault->delete();
			AssaultUser::query()->where('assault_id', $fleet->assault_id)->delete();
		}

		$fleet->update([
			'start_time'	=> time() - 1,
			'end_stay' 		=> 0,
			'end_time' 		=> $ReturnFlyingTime + 1,
			'target_user_id'=> $this->user->id,
			'assault_id' 	=> null,
			'updated_at' 	=> $ReturnFlyingTime + 1,
			'mess' 			=> 1,
		]);

		throw new RedirectException(__('fleet.fl_isback'), '/xnova/');
	}
}
