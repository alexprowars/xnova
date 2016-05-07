<?php
namespace App\Controllers\Admin;

use App\Controllers\AdminController;

class ChangePassword
{
	public function show (AdminController $controller)
	{
		if ($controller->user->authlevel == 3)
		{
			if ($_POST)
			{
				if ($controller->request->getPost('password', 'string', '') != "" || $controller->request->getPost('username', 'string', '') != "")
				{
					$info = $controller->db->query("SELECT `id` FROM game_users WHERE `username` = '" . $controller->request->getPost('username') . "'")->fetch();

					if (isset($info['id']))
					{
						$controller->db->query("UPDATE game_users_info SET `password` = '" . md5($controller->request->getPost('password')) . "' WHERE `id` = '" . $info['id'] . "';");

						$controller->message('Пароль успешно изменён.', 'Успех', '/admin/md5changepass/', 3);
					}
					else
						$controller->message('Такого игрока несуществует.', 'Ошибка', '/admin/md5changepass/', 3);
				}
				else
					$controller->message('Не введён логин игрока или новый пароль.', 'Ошибка', '/admin/md5changepass/', 3);
			}

			$controller->view->pick('admin/changepass');
			$controller->tag->setTitle('Смена пароля');
		}
		else
			$controller->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));
	}
}

?>