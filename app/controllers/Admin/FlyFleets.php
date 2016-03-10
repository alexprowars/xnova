<?php
namespace App\Controllers\Admin;

use App\Controllers\AdminController;
use App\Fleet;
use App\Lang;

class FlyFleets
{
	public function show (AdminController $controller)
	{
		if ($controller->user->authlevel < 3)
			$controller->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));

		Lang::includeLang('admin/fleets');

		$table = [];

		$FlyingFleets = $controller->db->query("SELECT * FROM game_fleets ORDER BY `fleet_end_time` ASC;");

		while ($CurrentFleet = $FlyingFleets->fetch())
		{
			$Bloc['Id'] = $CurrentFleet['fleet_id'];
			$Bloc['Mission'] = Fleet::CreateFleetPopupedMissionLink($CurrentFleet, _getText('type_mission', $CurrentFleet['fleet_mission']), '');
			$Bloc['Mission'] .= "<br>" . (($CurrentFleet['fleet_mess'] == 1) ? "R" : "A");

			$Bloc['Fleet'] = Fleet::CreateFleetPopupedFleetLink($CurrentFleet, _getText('tech', 200), '', $controller->user);
			$Bloc['St_Owner'] = "[" . $CurrentFleet['fleet_owner'] . "]<br>" . $CurrentFleet['fleet_owner_name'];
			$Bloc['St_Posit'] = "[" . $CurrentFleet['fleet_start_galaxy'] . ":" . $CurrentFleet['fleet_start_system'] . ":" . $CurrentFleet['fleet_start_planet'] . "]<br>" . (($CurrentFleet['fleet_start_type'] == 1) ? "[P]" : (($CurrentFleet['fleet_start_type'] == 2) ? "D" : "L")) . "";
			$Bloc['St_Time'] = $controller->game->datezone('H:i:s d/n/Y', $CurrentFleet['fleet_start_time']);

			if (!empty($CurrentFleet['fleet_target_owner']))
				$Bloc['En_Owner'] = "[" . $CurrentFleet['fleet_target_owner'] . "]<br>" . $CurrentFleet['fleet_target_owner_name'];
			else
				$Bloc['En_Owner'] = "";

			$Bloc['En_Posit'] = "[" . $CurrentFleet['fleet_end_galaxy'] . ":" . $CurrentFleet['fleet_end_system'] . ":" . $CurrentFleet['fleet_end_planet'] . "]<br>" . (($CurrentFleet['fleet_end_type'] == 1) ? "[P]" : (($CurrentFleet['fleet_end_type'] == 2) ? "D" : "L")) . "";

			$Bloc['En_Time'] = $controller->game->datezone('H:i:s d/n/Y', $CurrentFleet['fleet_end_time']);

			$table[] = $Bloc;
		}

		$controller->view->pick('admin/fleets');
		$controller->view->setVar('flt_table', $table);
		$controller->tag->setTitle(_getText('flt_title'));
	}
}

?>