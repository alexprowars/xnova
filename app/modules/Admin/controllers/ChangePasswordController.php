<?php
namespace Xnova\Admin\Controllers;

class ChangePasswordController extends Application
{
	public function indexAction ()
	{
		if ($this->user->authlevel == 3)
		{
			if ($_POST)
			{
				if ($this->request->getPost('password', 'string', '') != "" || $this->request->getPost('username', 'string', '') != "")
				{
					$info = $this->db->query("SELECT `id` FROM game_users WHERE `username` = '" . $this->request->getPost('username') . "'")->fetch();

					if (isset($info['id']))
					{
						$this->db->query("UPDATE game_users_info SET `password` = '" . md5($this->request->getPost('password')) . "' WHERE `id` = '" . $info['id'] . "';");

						$this->message('Пароль успешно изменён.', 'Успех', '/admin/md5changepass/', 3);
					}
					else
						$this->message('Такого игрока несуществует.', 'Ошибка', '/admin/md5changepass/', 3);
				}
				else
					$this->message('Не введён логин игрока или новый пароль.', 'Ошибка', '/admin/md5changepass/', 3);
			}

			$this->view->pick('admin/changepass');
			$this->tag->setTitle('Смена пароля');
		}
		else
			$this->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));
	}
}

?>