<?php

namespace Admin\Controllers;

use Admin\Controller;

/**
 * @RoutePrefix("/admin/email")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class EmailController extends Controller
{
	public function initialize ()
	{
		parent::initialize();

		if ($this->user->authlevel < 3)
			$this->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));
	}

	public function indexAction ()
	{
		if (isset($_GET['u']) && isset($_GET['email']))
		{
			$email = $this->db->query("SELECT user_id FROM game_log_email WHERE user_id = " . intval($_GET['u']) . " AND email = '" . addslashes($_GET['email']) . "' AND ok = 0;")->fetch();

			if (isset($email['user_id']))
			{
				$this->db->query("UPDATE game_users_info SET email = '" . addslashes($_GET['email']) . "' WHERE id = " . intval($_GET['u']) . ";");
				$this->db->query("UPDATE game_log_email SET ok = 1 WHERE user_id = " . intval($_GET['u']) . " AND email = '" . addslashes($_GET['email']) . "' AND ok = 0;");
			}
		}

		$planetes = '';
		$query = $this->db->query("SELECT e.*, u.username FROM game_log_email e LEFT JOIN game_users u ON u.id = e.user_id WHERE ok = 0");
		$i = 0;
		while ($u = $query->fetch())
		{
			$planetes .= "<tr>"
					. "<td>" . $u['username'] . "</td>"
					. "<td>" . $this->game->datezone("d.m H:i", $u['time']) . "</td>"
					. "<td>" . $u['email'] . "</td>"
					. "<td><a href=\"?set=admin&mode=email&u=" . $u['user_id'] . "&email=" . $u['email'] . "\">сменить</a></td>"
					. "</tr>";
			$i++;
		}

		$this->view->setVar('planetes', $planetes);
		$this->tag->setTitle('Список email');
	}
}