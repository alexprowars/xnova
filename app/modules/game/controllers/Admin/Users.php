<?php
namespace App\Controllers\Admin;

use App\Controllers\AdminController;
use App\Helpers;

class Users
{
	public function show (AdminController $controller)
	{
		$action = $controller->request->get('mode', 'string', '');
		$error = '';

		switch ($action)
		{
			case 'edit':

				$controller->view->pick('admin/users_edit');

				$info = $controller->db->query("SELECT * FROM game_users WHERE id = ".$controller->request->get('id', 'int', '0')."")->fetch();

				if (isset($info['id']))
				{
					if ($controller->request->getPost('save', 'string', '') != '')
					{
						if (!$controller->request->getPost('username', 'string', ''))
							$error = 'Не указано имя пользователя';
						else
						{
							$controller->user->saveData(
							[
								'group_id' 	=> Helpers::CheckString($controller->request->getPost('group_id', 'int', 0)),
								'username' 	=> Helpers::CheckString($controller->request->getPost('username', 'string', ''))
							], $info['id']);

							$controller->response->redirect('admin/users/action/edit/id/'.$info['id'].'/');
						}
					}

					$groups = $controller->db->extractResult($controller->db->query("SELECT * FROM game_users_groups WHERE 1 ORDER BY id ASC"));

					$controller->view->setVar('info', $info);
					$controller->view->setVar('groups', $groups);
				}

				break;

			default:

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
					$TypeSort = "u.id";

				$p = @intval($_GET['p']);
				if ($p < 1)
					$p = 1;

				$controller->view->pick('admin/users_list');

				$list = $controller->db->extractResult($controller->db->query("SELECT u.`id`, u.`username`, ui.`email`, u.`ip`, ui.`create_time`, u.`onlinetime`, u.`banned` FROM game_users u, game_users_info ui WHERE ui.id = u.id ORDER BY " . $TypeSort . " LIMIT " . (($p - 1) * 25) . ", 25"));

				$controller->view->setVar('list', $list);

				$total = $controller->db->fetchColumn("SELECT COUNT(*) FROM game_users");

				$controller->view->setVar('pagination', Helpers::pagination($total, 25, '?set=admin&mode=userlist', $p));
		}

		$controller->view->setVar('error', $error);
		$controller->tag->setTitle('Список пользователей');
	}
}

?>