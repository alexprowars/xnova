<?php

namespace Admin\Controllers;

use Admin\Controller;

class UnbannedController extends Controller
{
	public function initialize ()
	{
		parent::initialize();

		if ($this->user->authlevel < 1)
			$this->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));
	}

	public function indexAction ()
	{
		if ($this->request->getPost('username', 'string', '') != '')
		{
			$info = $this->db->query("SELECT id, username, banned, vacation FROM game_users WHERE username = '".addslashes($this->request->getPost('username', 'string', ''))."';")->fetch();

			if (isset($info['id']))
			{
				$this->db->query("DELETE FROM game_banned WHERE who = '" . $info['id'] . "'");
				$this->db->query("UPDATE game_users SET banned = 0 WHERE id = '" . $info['id'] . "'");

				if ($info['vacation'] == 1)
					$this->db->query("UPDATE game_users SET vacation = 0 WHERE id = '" . $info['id'] . "'");

				$this->message("Игрок ".$info['username']." разбанен!", 'Информация');
			}
			else
				$this->message("Игрок не найден!", 'Информация');
		}

		$this->tag->setTitle('Разблокировка');
	}
}