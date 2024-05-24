<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;

class AlliancesController extends Controller
{
	public static function getMenu()
	{
		return [[
			'code'	=> 'alliances',
			'title' => 'Список альянсов',
			'icon'	=> 'hand-grab-o',
			'sort'	=> 120
		]];
	}

	public function index()
	{
		$query = $this->db->query("SELECT a.`id`, a.`name`, a.`tag`,  a.`owner`, a.`create_time`, a.`description`, a.`text`, a.`members`, u.`username` FROM alliances a, users u WHERE u.id = a.owner");

		$parse = [];
		$parse['alliance'] = [];

		$parse['desc'] = '';
		$parse['edit'] = '';
		$parse['name'] = '';
		$parse['member'] = '';
		$parse['member_row'] = '';
		$parse['mail'] = '';
		$parse['leader'] = '';

		while ($u = $query->fetch()) {
			$parse['alliance'][] = $u;
		}

		if (isset($_GET['desc'])) {
			$ally_id = intval($_GET['desc']);
			$info = $this->db->query("SELECT `description` FROM alliances WHERE id='" . $ally_id . "'");
			$ally_text = $info->fetch();

			$parse['desc'] = "<tr>"
					. "<th colspan=9>Описание альянса</th></tr>"
					. "<tr>"
					. "<td class=b colspan=9>" . $ally_text['description'] . "</td>"
					. "</tr>";
		}

		if (isset($_GET['edit'])) {
			$ally_id = intval($_GET['edit']);
			$info = $this->db->query("SELECT `description` FROM alliances WHERE id='" . $ally_id . "'");
			$ally_text = $info->fetch();

			$parse['desc'] = "<tr>"
					. "<th colspan=9>Реактирование описание альянса</th></tr>"
					. "<tr>"
					. "<form action=?set=admin&mode=alliancelist&edit=" . $ally_id . " method=POST>"
					. "<td class=b colspan=9><center><b><textarea name=desc cols=50 rows=10 >" . $ally_text['description'] . "</textarea></center></b></td>"
					. "</tr>"
					. "<tr>"
					. "<td class=b colspan=9><center><b><input type=submit value=Speichern></center></b></td>"
					. "</form></tr>";

			if (isset($_POST['desc'])) {
				if (!$this->access->canWriteController(self::CODE, 'admin')) {
					throw new \Exception('Access denied');
				}

				$this->db->query("UPDATE alliances SET `description` = '" . addslashes($_POST['desc']) . "' WHERE `id` = '" . intval($_GET['edit']) . "'");

				$this->response->redirect('admin/alliancelist/');
			}
		}


		if (isset($_GET['allyname'])) {
			$ally_id = intval($_GET['allyname']);

			$u = $this->db->query("SELECT `image`, `web`, `name`, `tag` FROM alliances WHERE `id` = '" . $ally_id . "'")->fetch();

			$parse['name'] = "<tr>"
					. "<td colspan=9 class=c>Название / обозначение / лого / сайт</td></tr>"
					. "<form action=?set=admin&mode=alliancelist&allyname=" . $ally_id . " method=POST>"
					. "<tr>"
					. "<th colspan=4><center><b>Название альянса</center></b></th>   <th colspan=5><center><b><input type=text name=name value='" . addslashes($u['name']) . "'></center></b></th>"
					. "</tr>"
					. "<tr>"
					. "<th colspan=4><center><b>Обозначение</center></b></th>   <th colspan=5><center><b><input type=text name=tag value=" . $u['tag'] . "></center></b></th>"
					. "</tr>"
					. "<tr>"
					. "<th colspan=3><center><b>Логотип альянса</center></b></th>   <th colspan=3><center><b><input type=text size=38 name=image value=" . $u['image'] . "></center></b></th>  <th colspan=3><center><b><a href=" . $u['image'] . ">Смотреть</a></center></b></th>"
					. "</tr>"
					. "<tr>"
					. "<th colspan=3><center><b>Сайт альянса</center></b></th>   <th colspan=3><center><b><input type=text size=38 name=web value=" . $u['web'] . "></center></b></th>  <th colspan=3><center><b><a href=" . $u['web'] . ">Смотреть</a></center></b></th>"
					. "</tr>"
					. "<tr>"
					. "<td class=b colspan=9><center><b><input type=submit value=Сохранить></center></b></td>"
					. "</form></tr>";

			if (isset($_POST['name'])) {
				if (!$this->access->canWriteController(self::CODE, 'admin')) {
					throw new \Exception('Access denied');
				}

				$this->db->query("UPDATE alliances SET `name` = '" . addslashes($_POST['name']) . "', `tag` = '" . addslashes($_POST['tag']) . "', `image` = '" . addslashes($_POST['image']) . "', `web` = '" . addslashes($_POST['web']) . "' WHERE `id` = '" . intval($_GET['allyname']) . "'");
				$this->response->redirect('admin/alliancelist/');
			}
		}

		if (isset($_GET['mitglieder'])) {
			$ally_id = intval($_GET['mitglieder']);

			$users = $this->db->query("SELECT `id`, `username` FROM users WHERE alliance_id='" . $ally_id . "'");

			$parse['member_row'] = '';

			$i = 0;
			while ($u = $users->fetch()) {
				$parse['member_row'] .= "<tr>"
						. "<td class=b colspan=2><center><b>" . $u['id'] . "</center></b></td>"
						. "<td class=b  colspan=5><center><b><a href=?set=messages&mode=write&id=" . $u['id'] . ">" . $u['username'] . "</a></center></b></td>"
						. "<td class=b  colspan=2><center><b><a href=?set=admin&mode=alliancelist&ent=" . $u['id'] . "> X </a></center></b></td>"
						. "</tr>";
				$i++;
			}
		}

		if (isset($_GET['ent'])) {
			$user_id = intval($_GET['ent']);

			$parse['name'] .= "<tr>"
					. "<th colspan=9>Удаление участника из альянса</th></tr>"
					. "<form action=?set=admin&mode=alliancelist&ent=" . $user_id . " method=POST>"
					. "<tr>"
					. "<th colspan=9><center><b>После нажатия кнопки Удалить, выбранный вами участник выйдет из альянса. <br>Ты действительно хочешь это сделать?</center></b></th>"
					. "</tr>"
					. "<td class=b colspan=9><center><b><input type=submit value=Удалить name=ent></center></b></td>"
					. "</form></tr>";

			if (isset($_POST['ent'])) {
				if (!$this->access->canWriteController(self::CODE, 'admin')) {
					throw new \Exception('Access denied');
				}

				$user_id = $_GET['ent'];
				$this->db->query("UPDATE users SET `alliance_id`=0, `alliance_name` = '' WHERE `id`='" . $user_id . "'");
				$this->response->redirect('admin/alliancelist/');
			}
		}

		if (isset($_GET['mail'])) {
			$ally_id = $_GET['mail'];

			$parse['mail'] = "<tr>"
					. "<th colspan=9>Собщение участникам альянса</th></tr>"
					. "<tr>"
					. "<form action=?set=admin&mode=alliancelist&mail=" . $ally_id . " method=POST>"
					. "<tr>"
					. "<td class=b colspan=9><center><b><textarea name=text cols=50 rows=10 ></textarea></center></b></td>"
					. "</tr>"
					. "<tr>"
					. "<td class=b colspan=9><center><b><input type=submit value=Отправить></center></b></td>"
					. "</form></tr>";

			if (isset($_POST['text'])) {
				if (!$this->access->canWriteController(self::CODE, 'admin')) {
					throw new \Exception('Access denied');
				}

				$ally_id = intval($_GET['mail']);
				$sq = $this->db->query("SELECT id FROM users WHERE alliance_id='" . $ally_id . "'");
				while ($u = $sq->fetch()) {
					$this->db->query("INSERT INTO messages SET
											`owner`='{$u['id']}',
											`sender`='Администрация' ,
											`time`='" . time() . "',
											`type`='2',
											`from`='Сообщение альянса (Admin)',
											`text`='" . addslashes($_POST['text']) . "'
											");
				}
				$this->response->redirect('admin/alliancelist/');
			}
		}

		if (isset($_GET['leader'])) {
			$ally_id = intval($_GET['leader']);

			$query = $this->db->query("SELECT `user_id` FROM alliances");
			$u = $query->fetch();
			$users = $this->db->query("SELECT `username` FROM users WHERE id='" . $u['owner'] . "'");
			$a = $users->fetch();
			$leader = $a['username'];

			$parse['leader'] = "<tr>"
					. "<td colspan=9 class=c>Смена лидера альянса</td></tr>"
					. "<form action=?set=admin&mode=alliancelist&leader=" . $ally_id . " method=POST>"
					. "<tr>"
					. "<th colspan=4><center><b>Сейчас лидер:</center></b></th>   <th colspan=5><center><b>$leader</center></b></th>"
					. "</tr>"
					. "<tr>"
					. "<th colspan=4><center><b><u>ID</u> нового лидера</center></b></th>   <th colspan=5><center><b><input type=text size=8 name=leader></center></b></th>"
					. "</tr>"
					. "<tr>"
					. "<td class=b colspan=9><center><b><input type=submit value=Сохранить></center></b></td>"
					. "</form></tr>";

			if (isset($_POST['leader'])) {
				if (!$this->access->canWriteController(self::CODE, 'admin')) {
					throw new \Exception('Access denied');
				}

				$sq = $this->db->query("SELECT alliance_id FROM users WHERE id='" . intval($_POST['leader']) . "'");
				$a = $sq->fetch();

				if ($a['alliance_id'] == $_GET['leader']) {
					$this->db->query("UPDATE alliances SET `user_id` = '" . intval($_POST['leader']) . "' WHERE `id` = '" . intval($_GET['leader']) . "'");
				}

				$this->response->redirect('admin/alliancelist/');
			}
		}

		$this->view->setVar('parse', $parse);
		View::share('title', 'Список альянсов');
	}
}
