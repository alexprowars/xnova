<?php

namespace Admin\Controllers;

use Admin\Controller;
use App\Helpers;

class UsersController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
	}

	public function indexAction ()
	{
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

		$list = $this->db->extractResult($this->db->query("SELECT u.`id`, u.`username`, ui.`email`, u.`ip`, ui.`create_time`, u.`onlinetime`, u.`banned` FROM game_users u, game_users_info ui WHERE ui.id = u.id ORDER BY " . $TypeSort . " LIMIT " . (($p - 1) * 25) . ", 25"));

		$this->view->setVar('list', $list);

		$total = $this->db->fetchColumn("SELECT COUNT(*) FROM game_users");

		$this->view->setVar('pagination', Helpers::pagination($total, 25, '/admin/users/', $p));

		$this->tag->setTitle('Список пользователей');
	}

	public function editAction ($id)
	{
		$error = '';

		$info = $this->db->query("SELECT * FROM game_users WHERE id = ".intval($id)."")->fetch();

		if (isset($info['id']))
		{
			if ($this->request->getPost('save', 'string', '') != '')
			{
				if (!$this->request->getPost('username', 'string', ''))
					$error = 'Не указано имя пользователя';
				else
				{
					$this->user->saveData(
					[
						'group_id' 	=> Helpers::CheckString($this->request->getPost('group_id', 'int', 0)),
						'username' 	=> Helpers::CheckString($this->request->getPost('username', 'string', ''))
					], $info['id']);

					$this->response->redirect('admin/users/edit/'.$info['id'].'/');
				}
			}

			$groups = $this->db->extractResult($this->db->query("SELECT * FROM game_users_groups WHERE 1 ORDER BY id ASC"));

			$this->view->setVar('info', $info);
			$this->view->setVar('groups', $groups);
		}

		$this->view->setVar('error', $error);
	}
}