<?php
namespace App\Controllers\Fleet;

use App\Controllers\FleetController;
use App\Lang;

class Back
{
	public function show (FleetController $controller)
	{
		Lang::includeLang('fleet');

		$BoxTitle = _getText('fl_error');
		$TxtColor = "red";
		$BoxMessage = _getText('fl_notback');

		if ($controller->request->hasPost('fleetid'))
		{
			$fleetid = $controller->request->getPost('fleetid', 'int', 0);

			$FleetRow = $controller->db->query("SELECT * FROM game_fleets WHERE `fleet_id` = '" . $fleetid . "';")->fetch();

			if ($FleetRow['fleet_owner'] == $controller->user->id)
			{
				if (($FleetRow['fleet_mess'] == 0 || ($FleetRow['fleet_mess'] == 3 && $FleetRow['fleet_mission'] != 15) && $FleetRow['fleet_mission'] != 20 && $FleetRow['fleet_target_owner'] != 1))
				{
					if ($FleetRow['fleet_end_stay'] != 0)
					{

						if ($FleetRow['fleet_start_time'] > time())
							$CurrentFlyingTime = time() - $FleetRow['start_time'];
						else
							$CurrentFlyingTime = $FleetRow['fleet_start_time'] - $FleetRow['start_time'];
					}
					else
						$CurrentFlyingTime = time() - $FleetRow['start_time'];

					$ReturnFlyingTime = $CurrentFlyingTime + time();

					$controller->db->updateAsDict('game_fleets',
					[
						'fleet_start_time'	 	=> time() - 1,
						'fleet_end_stay' 		=> 0,
						'fleet_end_time' 		=> $ReturnFlyingTime + 1,
						'fleet_target_owner' 	=> $controller->user->id,
						'fleet_group' 			=> 0,
						'fleet_time' 			=> $ReturnFlyingTime + 1,
						'fleet_mess' 			=> 1,
					], 'fleet_id = '.$fleetid);

					if ($FleetRow['fleet_group'] != 0 && $FleetRow['fleet_mission'] == 1)
					{
						$controller->db->delete('game_aks', 'id = ?', [$FleetRow['fleet_group']]);
						$controller->db->delete('game_aks_user', 'aks_id = ?', [$FleetRow['fleet_group']]);
					}

					$BoxTitle = _getText('fl_sback');
					$TxtColor = "lime";
					$BoxMessage = _getText('fl_isback');
				}
				else
					$BoxMessage = _getText('fl_notback');
			}
			else
				$BoxMessage = _getText('fl_onlyyours');
		}

		$controller->message("<font color=\"" . $TxtColor . "\">" . $BoxMessage . "</font>", $BoxTitle, "/fleet/", 2);
	}
}

?>