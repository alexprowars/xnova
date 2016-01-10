<?php
namespace App\Controllers\Admin;

use App\Controllers\AdminController;

class Unbanned
{
	public function show (AdminController $controller)
	{
		if ($controller->user->authlevel >= 1)
		{
			if ($controller->request->getPost('username', 'string', '') != '')
			{
				$info = $controller->db->query("SELECT id, username, banned, vacation FROM game_users WHERE username = '".addslashes($controller->request->getPost('username', 'string', ''))."';")->fetch();

				if (isset($info['id']))
				{
					$controller->db->query("DELETE FROM game_banned WHERE who = '" . $info['id'] . "'");
					$controller->db->query("UPDATE game_users SET banned = 0 WHERE id = '" . $info['id'] . "'");

					if ($info['vacation'] == 1)
						$controller->db->query("UPDATE game_users SET vacation = 0 WHERE id = '" . $info['id'] . "'");

					$controller->message("Игрок ".$info['username']." разбанен!", 'Информация');
				}
				else
					$controller->message("Игрок не найден!", 'Информация');
			}

			$controller->view->pick('admin/unbanned');
			$controller->tag->setTitle('Разблокировка');
		}
		else
			$controller->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));
	}
}

?>