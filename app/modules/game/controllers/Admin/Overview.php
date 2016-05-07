<?php
namespace App\Controllers\Admin;

use App\Controllers\AdminController;
use App\Helpers;

class Overview
{
	public function show (AdminController $controller)
	{
		if (isset($_GET['cmd']) && $_GET['cmd'] == 'sort')
			$TypeSort = $_GET['type'];
		else
			$TypeSort = "ip";

		$parse = [];
		$parse['adm_ov_data_yourv'] = VERSION;
		$parse['adm_ov_data_table'] = [];

		$Count = 0;
		$Color = "lime";
		$PrevIP = '';

		if ($controller->user->authlevel >= 2)
		{
			$Last15Mins = $controller->db->query("SELECT `id`, `username`, `ip`, `ally_name`, `onlinetime` FROM game_users WHERE `onlinetime` >= '" . (time() - 15 * 60) . "' ORDER BY `" . $TypeSort . "` ASC;");

			while ($TheUser = $Last15Mins->fetch())
			{
				if ($PrevIP != "")
				{
					if ($PrevIP == $TheUser['ip'])
						$Color = "red";
					else
						$Color = "lime";
				}

				$PrevIP = $TheUser['ip'];

				$Bloc['adm_ov_altpm'] = _getText('adm_ov_altpm');
				$Bloc['adm_ov_wrtpm'] = _getText('adm_ov_wrtpm');
				$Bloc['adm_ov_data_id'] = $TheUser['id'];
				$Bloc['adm_ov_data_name'] = $TheUser['username'];
				$Bloc['adm_ov_data_clip'] = $Color;
				$Bloc['adm_ov_data_adip'] = long2ip($TheUser['ip']);
				$Bloc['adm_ov_data_ally'] = $TheUser['ally_name'];
				$Bloc['adm_ov_data_activ'] = Helpers::pretty_time(time() - $TheUser['onlinetime']);

				$parse['adm_ov_data_table'][] = $Bloc;
				$Count++;
			}
		}

		$parse['adm_ov_data_count'] = $Count;

		$controller->view->pick('admin/overview');
		$controller->view->setVar('parse', $parse);
		$controller->tag->setTitle('Активность на сервере');
	}
}

?>