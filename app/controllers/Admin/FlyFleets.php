<?php
namespace App\Controllers\Admin;

use App\Controllers\AdminController;
use App\Fleet;
use App\Lang;
use App\Models\Fleet as FleetModel;

class FlyFleets
{
	public function show (AdminController $controller)
	{
		if ($controller->user->authlevel < 3)
			$controller->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));

		Lang::includeLang('admin/fleets');

		$table = [];

		$FlyingFleets = FleetModel::find(['order' => 'end_time asc']);

		foreach ($FlyingFleets as $CurrentFleet)
		{
			$Bloc['Id'] = $CurrentFleet->id;
			$Bloc['Mission'] = Fleet::CreateFleetPopupedMissionLink($CurrentFleet, _getText('type_mission', $CurrentFleet->mission), '');
			$Bloc['Mission'] .= "<br>" . (($CurrentFleet->mess == 1) ? "R" : "A");

			$Bloc['Fleet'] = Fleet::CreateFleetPopupedFleetLink($CurrentFleet, _getText('tech', 200), '', $controller->user);
			$Bloc['St_Owner'] = "[" . $CurrentFleet->owner . "]<br>" . $CurrentFleet->owner_name;
			$Bloc['St_Posit'] = "[" . $CurrentFleet->start_galaxy . ":" . $CurrentFleet->start_system . ":" . $CurrentFleet->start_planet . "]<br>" . (($CurrentFleet->start_type == 1) ? "[P]" : (($CurrentFleet->start_type == 2) ? "D" : "L")) . "";
			$Bloc['St_Time'] = $controller->game->datezone('H:i:s d/n/Y', $CurrentFleet['start_time']);

			if (!empty($CurrentFleet['target_owner']))
				$Bloc['En_Owner'] = "[" . $CurrentFleet->target_owner . "]<br>" . $CurrentFleet->target_owner_name;
			else
				$Bloc['En_Owner'] = "";

			$Bloc['En_Posit'] = "[" . $CurrentFleet->end_galaxy . ":" . $CurrentFleet->end_system . ":" . $CurrentFleet->end_planet . "]<br>" . (($CurrentFleet->end_type == 1) ? "[P]" : (($CurrentFleet->end_type == 2) ? "D" : "L")) . "";

			$Bloc['En_Time'] = $controller->game->datezone('H:i:s d/n/Y', $CurrentFleet->end_time);

			$table[] = $Bloc;
		}

		$controller->view->pick('admin/fleets');
		$controller->view->setVar('flt_table', $table);
		$controller->tag->setTitle(_getText('flt_title'));
	}
}

?>