<?php
namespace App\Controllers\Fleet;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use App\Controllers\FleetController;
use App\Lang;
use App\Models\Fleet;

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

			/**
			 * @var $FleetRow \App\Models\Fleet
			 */
			$FleetRow = Fleet::findFirst($fleetid);

			if ($FleetRow && $FleetRow->owner == $controller->user->id)
			{
				if (($FleetRow->mess == 0 || ($FleetRow->mess == 3 && $FleetRow->mission != 15) && $FleetRow->mission != 20 && $FleetRow->target_owner != 1))
				{
					if ($FleetRow->end_stay != 0)
					{
						if ($FleetRow->start_time > time())
							$CurrentFlyingTime = time() - $FleetRow->create_time;
						else
							$CurrentFlyingTime = $FleetRow->start_time - $FleetRow->create_time;
					}
					else
						$CurrentFlyingTime = time() - $FleetRow->create_time;

					$ReturnFlyingTime = $CurrentFlyingTime + time();

					if ($FleetRow->group_id != 0 && $FleetRow->mission == 1)
					{
						$controller->db->delete('game_aks', 'id = ?', [$FleetRow->group_id]);
						$controller->db->delete('game_aks_user', 'aks_id = ?', [$FleetRow->group_id]);
					}

					$FleetRow->update([
						'start_time'	=> time() - 1,
						'end_stay' 		=> 0,
						'end_time' 		=> $ReturnFlyingTime + 1,
						'target_owner' 	=> $controller->user->id,
						'group_id' 		=> 0,
						'update_time' 	=> $ReturnFlyingTime + 1,
						'mess' 			=> 1,
					]);

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