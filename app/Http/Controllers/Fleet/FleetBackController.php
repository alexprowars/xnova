<?php

namespace App\Http\Controllers\Fleet;

use App\Exceptions\Exception;
use App\Http\Controllers\Controller;
use App\Models\Fleet;
use App\Services\FleetService;
use Illuminate\Http\Request;

class FleetBackController extends Controller
{
	public function index(Request $request): void
	{
		$fleetId = (int) $request->post('id', 0);

		if ($fleetId <= 0) {
			throw new Exception(__('fleet.fleet_not_selected'));
		}

		$fleet = Fleet::find($fleetId);

		if (!$fleet || $fleet->user_id != $this->user->id) {
			throw new Exception(__('fleet.onlyyours'));
		}

		FleetService::recall($fleet);
	}
}
