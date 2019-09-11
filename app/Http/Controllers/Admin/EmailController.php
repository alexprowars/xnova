<?php

namespace Xnova\Http\Controllers\Admin;

use Illuminate\Support\Facades\View;
use Xnova\AdminController;

class EmailController extends AdminController
{
	public static function getMenu ()
	{
		return [[
			'code'	=> 'email',
			'title' => 'Сменить email',
			'icon'	=> 'envelope',
			'sort'	=> 160
		]];
	}

	public function index ()
	{
		if (isset($_GET['u']) && isset($_GET['email']))
		{
			$email = $this->db->query("SELECT user_id FROM log_email WHERE user_id = " . intval($_GET['u']) . " AND email = '" . addslashes($_GET['email']) . "' AND ok = 0;")->fetch();

			if (isset($email['user_id']))
			{
				$this->db->query("UPDATE users_info SET email = '" . addslashes($_GET['email']) . "' WHERE id = " . intval($_GET['u']) . ";");
				$this->db->query("UPDATE log_email SET ok = 1 WHERE user_id = " . intval($_GET['u']) . " AND email = '" . addslashes($_GET['email']) . "' AND ok = 0;");
			}
		}

		$planetes = '';
		$query = $this->db->query("SELECT e.*, u.username FROM log_email e LEFT JOIN users u ON u.id = e.user_id WHERE ok = 0");
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

		View::share('title', 'Список email');

		return view('admin.email', ['planetes' => $planetes]);
	}
}