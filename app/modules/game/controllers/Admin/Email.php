<?php
namespace App\Controllers\Admin;

use App\Controllers\AdminController;

class Email
{
	public function show (AdminController $controller)
	{
		if ($controller->user->authlevel >= 3)
		{
			if (isset($_GET['u']) && isset($_GET['email']))
			{
				$email = $controller->db->query("SELECT user_id FROM game_log_email WHERE user_id = " . intval($_GET['u']) . " AND email = '" . addslashes($_GET['email']) . "' AND ok = 0;")->fetch();

				if (isset($email['user_id']))
				{
					$controller->db->query("UPDATE game_users_info SET email = '" . addslashes($_GET['email']) . "' WHERE id = " . intval($_GET['u']) . ";");
					$controller->db->query("UPDATE game_log_email SET ok = 1 WHERE user_id = " . intval($_GET['u']) . " AND email = '" . addslashes($_GET['email']) . "' AND ok = 0;");
				}
			}

			$planetes = '';
			$query = $controller->db->query("SELECT e.*, u.username FROM game_log_email e LEFT JOIN game_users u ON u.id = e.user_id WHERE ok = 0");
			$i = 0;
			while ($u = $query->fetch())
			{
				$planetes .= "<tr>"
						. "<td>" . $u['username'] . "</td>"
						. "<td>" . $controller->game->datezone("d.m H:i", $u['time']) . "</td>"
						. "<td>" . $u['email'] . "</td>"
						. "<td><a href=\"?set=admin&mode=email&u=" . $u['user_id'] . "&email=" . $u['email'] . "\">сменить</a></td>"
						. "</tr>";
				$i++;
			}

			$controller->view->pick('admin/email');
			$controller->view->setVar('planetes', $planetes);
			$controller->tag->setTitle('Список email');
		}
		else
			$controller->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));
	}
}

?>