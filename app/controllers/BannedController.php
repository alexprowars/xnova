<?php

namespace App\Controllers;

class BannedController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();
	}

	public function indexAction ()
	{
		$query = $this->db->query('SELECT u.username AS user_1, u2.username AS user_2, b.* FROM game_banned b LEFT JOIN game_users u ON u.id = b.who LEFT JOIN game_users u2 ON u2.id = b.author ORDER BY b.`id` DESC');

		$bannedList = array();

		while ($u = $query->fetch())
		{
			$bannedList[] = $u;
		}

		$this->view->pick('banned');
		$this->view->setVar('bannedList', $bannedList);

		$this->tag->setTitle('Список заблокированных игроков');
		$this->showTopPanel(false);
		$this->showLeftPanel(($this->user->getId() && $this->user->banned == 0));
	}
}

?>