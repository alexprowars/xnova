<?php

namespace App\Http\Controllers\Fleet;

use App\Exceptions\ErrorException;
use App\Exceptions\RedirectException;
use App\Http\Controllers\Controller;
use App\Models\Fleet;
use Illuminate\Http\Request;

class FleetBackController extends Controller
{
	public function index(Request $request)
	{
		$fleetId = (int) $request->post('id', 0);

		if ($fleetId <= 0) {
			throw new ErrorException('Не выбран флот');
		}

		$fleet = Fleet::find($fleetId);

		if (!$fleet || $fleet->user_id != $this->user->id) {
			throw new ErrorException(__('fleet.fl_onlyyours'));
		}

		if (!$fleet->canBack()) {
			throw new ErrorException(__('fleet.fl_notback'));
		}

		if ($fleet->end_stay) {
			if ($fleet->start_time->isFuture()) {
				$currentFlyingTime = now()->sub($fleet->created_at);
			} else {
				$currentFlyingTime = $fleet->start_time->sub($fleet->created_at);
			}
		} else {
			$currentFlyingTime = now()->sub($fleet->created_at);
		}

		$returnFlyingTime = $currentFlyingTime->add(now());

		if ($fleet->mission == 1 && $fleet->assault) {
			$fleet->assault->delete();
		}

		$fleet->update([
			'start_time'		=> now()->subSecond(),
			'end_stay' 			=> 0,
			'end_time' 			=> $returnFlyingTime + 1,
			'target_user_id'	=> $this->user->id,
			'assault_id' 		=> null,
			'updated_at' 		=> $returnFlyingTime + 1,
			'mess' 				=> 1,
		]);

		throw new RedirectException('/', __('fleet.fl_isback'));
	}
}
