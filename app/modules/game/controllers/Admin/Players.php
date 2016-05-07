<?php
namespace App\Controllers\Admin;

use App\Controllers\AdminController;
use App\Helpers;
use App\Lang;

class Players
{
	public function show (AdminController $controller)
	{
		if ($controller->user->authlevel > 2)
		{
			Lang::includeLang('admin');

			if (isset($_GET['cmd']) && $_GET['cmd'] == 'sort')
			{
				if ($_GET['type'] == 'id')
					$TypeSort = "u.id";
				elseif ($_GET['type'] == 'username')
					$TypeSort = "u.username";
				elseif ($_GET['type'] == 'email')
					$TypeSort = "ui.email";
				elseif ($_GET['type'] == 'ip')
					$TypeSort = "u.ip";
				elseif ($_GET['type'] == 'create_time')
					$TypeSort = "ui.create_time";
				elseif ($_GET['type'] == 'onlinetime')
					$TypeSort = "u.onlinetime";
				elseif ($_GET['type'] == 'banned')
					$TypeSort = "u.banned";
				else
					$TypeSort = "u.id";
			}
			else
			{
				$TypeSort = "u.id";
			}

			$p = @intval($_GET['p']);
			if ($p < 1)
				$p = 1;

			$query = $controller->db->query("SELECT u.`id`, u.`username`, ui.`email`, u.`ip`, ui.`create_time`, u.`onlinetime`, u.`banned` FROM game_users u, game_users_info ui WHERE ui.id = u.id ORDER BY " . $TypeSort . " LIMIT " . (($p - 1) * 25) . ", 25");

			$parse = [];
			$parse['adm_ul_table'] = [];
			$Color = "lime";
			$PrevIP = '';

			while ($u = $query->fetch())
			{
				if ($PrevIP != "")
				{
					if ($PrevIP == $u['ip'])
					{
						$Color = "red";
					}
					else
					{
						$Color = "lime";
					}
				}

				$Bloc['adm_ul_data_id'] = $u['id'];
				$Bloc['adm_ul_data_name'] = $u['username'];
				$Bloc['adm_ul_data_mail'] = $u['email'];
				$Bloc['adm_ul_data_adip'] = "<font color=\"" . $Color . "\">" . long2ip($u['ip']) . "</font>";
				$Bloc['adm_ul_data_regd'] = date("d.m.Y H:i:s", $u['create_time']);
				$Bloc['adm_ul_data_lconn'] = date("d.m.Y H:i:s", $u['onlinetime']);
				$Bloc['adm_ul_data_banna'] = ($u['banned'] > 0) ? "<a href=\"#\" title=\"" . date("d.m.Y H:i:s", $u['banned']) . "\">" . _getText('adm_ul_yes') . "</a>" : _getText('adm_ul_no');

				$PrevIP = $u['ip'];

				$parse['adm_ul_table'][] = $Bloc;
			}

			$total = $controller->db->fetchColumn("SELECT COUNT(*) FROM game_users");

			$parse['adm_ul_count'] = Helpers::pagination($total, 25, '?set=admin&mode=userlist', $p);

			$controller->view->pick('admin/userlist');
			$controller->view->setVar('parse', $parse);
			$controller->tag->setTitle(_getText('adm_ul_title'));
		}
		else
			$controller->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));
	}
}

?>