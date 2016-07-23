<?php

namespace Admin\Controllers;

use Admin\Controller;
use App\Helpers;

class OverviewController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
	}

	public function indexAction ()
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

		if ($this->user->authlevel >= 2)
		{
			$Last15Mins = $this->db->query("SELECT `id`, `username`, `ip`, `ally_name`, `onlinetime` FROM game_users WHERE `onlinetime` >= '" . (time() - 15 * 60) . "' ORDER BY `" . $TypeSort . "` ASC;");

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

		$this->view->setVar('parse', $parse);
		$this->tag->setTitle('Активность на сервере');
	}
}