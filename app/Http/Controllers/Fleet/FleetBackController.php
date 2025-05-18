<?php

namespace App\Http\Controllers\Fleet;

use App\Engine\Fleet\Mission;
use App\Exceptions\Exception;
use App\Http\Controllers\Controller;
use App\Models\Fleet;
use Illuminate\Http\Request;

class FleetBackController extends Controller
{
	public function index(Request $request)
	{
		$fleetId = (int) $request->post('id', 0);

		if ($fleetId <= 0) {
			throw new Exception('Не выбран флот');
		}

		$fleet = Fleet::find($fleetId);

		if (!$fleet || $fleet->user_id != $this->user->id) {
			throw new Exception(__('fleet.fl_onlyyours'));
		}

		if (!$fleet->canBack()) {
			throw new Exception(__('fleet.fl_notback'));
		}

		if ($fleet->end_stay) {
			if ($fleet->start_date->isFuture()) {
				$flyingTime = $fleet->created_at->diffInSeconds(now());
			} else {
				$flyingTime = $fleet->created_at->diffInSeconds($fleet->created_at);
			}
		} else {
			$flyingTime = $fleet->created_at->diffInSeconds(now());
		}

		$returnTime = now()->toImmutable()->addSeconds($flyingTime);

		if ($fleet->mission == Mission::Attack && $fleet->assault) {
			$fleet->assault->delete();
		}

		$fleet->update([
			'start_date'		=> now()->subSecond(),
			'end_stay' 			=> null,
			'end_date' 			=> $returnTime->addSecond(),
			'target_user_id'	=> $this->user->id,
			'assault_id' 		=> null,
			'updated_at' 		=> $returnTime->addSecond(),
			'mess' 				=> 1,
		]);
	}
}
