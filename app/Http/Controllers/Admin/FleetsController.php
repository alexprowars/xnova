<?php

namespace Xnova\Http\Controllers\Admin;

use Illuminate\Support\Facades\View;
use Xnova\AdminController;
use Xnova\Fleet;
use Xnova\Models\Fleet as FleetModel;

class FleetsController extends AdminController
{
	public static function getMenu ()
	{
		return [[
			'code'	=> 'fleets',
			'title' => 'Флоты в полёте',
			'icon'	=> 'plane',
			'sort'	=> 110
		]];
	}

	public function index ()
	{
		$table = [];

		$FlyingFleets = FleetModel::find(['order' => 'end_time asc']);

		foreach ($FlyingFleets as $CurrentFleet)
		{
			$Bloc['Id'] = $CurrentFleet->id;
			$Bloc['Mission'] = Fleet::CreateFleetPopupedMissionLink($CurrentFleet, _getText('type_mission', $CurrentFleet->mission), '');
			$Bloc['Mission'] .= "<br>" . (($CurrentFleet->mess == 1) ? "R" : "A");

			$Bloc['Fleet'] = Fleet::CreateFleetPopupedFleetLink($CurrentFleet, _getText('tech', 200), '', $this->user);
			$Bloc['St_Owner'] = "[" . $CurrentFleet->owner . "]<br>" . $CurrentFleet->owner_name;
			$Bloc['St_Posit'] = "[" . $CurrentFleet->start_galaxy . ":" . $CurrentFleet->start_system . ":" . $CurrentFleet->start_planet . "]<br>" . (($CurrentFleet->start_type == 1) ? "[P]" : (($CurrentFleet->start_type == 2) ? "D" : "L")) . "";
			$Bloc['St_Time'] = $this->game->datezone('H:i:s d/n/Y', $CurrentFleet->start_time);

			if (!empty($CurrentFleet->target_owner))
				$Bloc['En_Owner'] = "[" . $CurrentFleet->target_owner . "]<br>" . $CurrentFleet->target_owner_name;
			else
				$Bloc['En_Owner'] = "";

			$Bloc['En_Posit'] = "[" . $CurrentFleet->end_galaxy . ":" . $CurrentFleet->end_system . ":" . $CurrentFleet->end_planet . "]<br>" . (($CurrentFleet->end_type == 1) ? "[P]" : (($CurrentFleet->end_type == 2) ? "D" : "L")) . "";

			$Bloc['En_Time'] = $this->game->datezone('H:i:s d/n/Y', $CurrentFleet->end_time);

			$table[] = $Bloc;
		}

		View::share('title', __('flt_title'));

		return view('admin.fleets', ['flt_table' => $table]);
	}
}