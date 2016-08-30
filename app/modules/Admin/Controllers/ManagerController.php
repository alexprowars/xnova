<?php

namespace Admin\Controllers;

use Admin\Controller;
use Friday\Core\Lang;

/**
 * @RoutePrefix("/admin/manager")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class ManagerController extends Controller
{
	const CODE = 'manager';

	public function initialize ()
	{
		parent::initialize();

		if (!$this->access->canReadController(self::CODE, 'admin'))
			throw new \Exception('Access denied');
	}

	public static function getMenu ()
	{
		return [[
			'code'	=> 'manager',
			'title' => 'Редактор',
			'icon'	=> 'magic-wand',
			'sort'	=> 70,
			'childrens' => [
				[
					'code'	=> 'ip',
					'title' => 'Поиск по IP',
				],
				[
					'code'	=> 'data',
					'title' => 'Статистика',
				],
				[
					'code'	=> 'level',
					'title' => 'Смена прав',
				]
			]
		]];
	}

	public function indexAction ()
	{
		$this->tag->setTitle(_getText('panel_mainttl'));
	}

	public function ipAction ()
	{
		if ($this->request->has('send'))
		{
			$Pattern = addslashes($_POST['ip']);
			$SelUser = $this->db->query("SELECT * FROM game_users WHERE ip = INET_ATON('" . $Pattern . "');");
			$parse = [];
			$parse['adm_this_ip'] = $Pattern;
			$parse['adm_plyer_lst'] = '';

			while ($Usr = $SelUser->fetch())
			{
				$UsrMain = $this->db->query("SELECT name FROM game_planets WHERE id = '" . $Usr['planet_id'] . "';")->fetch();
				$parse['adm_plyer_lst'] .= "<tr><th>" . $Usr['username'] . "</th><th>[" . $Usr['galaxy'] . ":" . $Usr['system'] . ":" . $Usr['planet'] . "] " . $UsrMain['name'] . "</th></tr>";
			}

			$this->view->pick('manager/adminpanel_ans2');
			$this->view->setVar('parse', $parse);
		}
	}

	public function dataAction ()
	{
		if ($this->request->has('send'))
		{
			$username = $this->request->get('username');

			$SelUser = $this->db->query("SELECT u.*, ui.* FROM game_users u, game_users_info ui WHERE ui.id = u.id AND ".(is_numeric($username) ? "u.id = '" . $username . "'" : "u.username = '" . $username . "'")." LIMIT 1;")->fetch();

			if (!isset($SelUser['id']))
				$this->message('Такого игрока не существует', 'Ошибка', '/admin/manager/', 2);

			$parse = [];
			$parse['answer1'] = $SelUser['id'];
			$parse['answer2'] = $SelUser['username'];
			$parse['answer3'] = long2ip($SelUser['ip']);
			$parse['answer4'] = $SelUser['email'];
			$parse['answer6'] = _getText('adm_usr_genre', $SelUser['sex']);
			$parse['answer7'] = date('d.m.Y H:i:s', $SelUser['vacation']);
			$parse['answer9'] = date('d.m.Y H:i:s', $SelUser['create_time']);
			$parse['answer8'] = "[" . $SelUser['galaxy'] . ":" . $SelUser['system'] . ":" . $SelUser['planet'] . "] ";

			$parse['planet_list'] = [];
			$parse['planet_fields'] = $this->registry->resource;

			$parse['planet_list'] = $this->db->extractResult($this->db->query("SELECT * FROM game_planets WHERE id_owner = '" . $SelUser['id'] . "' ORDER BY id ASC"));

			$parse['history_actions'] = [
				1 => 'Постройка здания',
				2 => 'Снос здания',
				3 => 'Отмена постройки',
				4 => 'Отмена сноса',
				5 => 'Исследование',
				6 => 'Отмена исследования',
				7 => 'Постройка обороны/флота',
			];

			$parse['transfer_list'] = [];

			$transfers = $this->db->extractResult($this->db->query("SELECT t.*, u.username AS target FROM game_log_transfers t LEFT JOIN game_users u ON u.id = t.target_id WHERE t.user_id = '" . $SelUser['id'] . "' ORDER BY id DESC"));

			foreach ($transfers AS $transfer)
			{
				preg_match("/s\:\[(.*?)\:(.*?)\:(.*?)\((.*?)\)\];e\:\[(.*?)\:(.*?)\:(.*?)\((.*?)\)\];f\:\[(.*?)\];m\:(.*?);c\:(.*?);d\:(.*?);/", $transfer['data'], $t);

				$parse['transfer_list'][] = [
					'time' 	=> $transfer['time'],
					'start' => '<a href="/?set=galaxy&r=3&galaxy='.$t[1].'&system='.$t[2].'&planet='.$t[3].'" target="_blank">'.$t[1].':'.$t[2].':'.$t[3].' ('._getText('type_planet', $t[4]).')</a>',
					'end' 	=> '<a href="/?set=galaxy&r=3&galaxy='.$t[5].'&system='.$t[6].'&planet='.$t[7].'" target="_blank">'.$t[5].':'.$t[6].':'.$t[7].' ('._getText('type_planet', $t[8]).')</a>',
					'metal'	=> $t[10],
					'crystal'	=> $t[11],
					'deuterium'	=> $t[12],
					'target'	=> $transfer['target'],
				];
			}

			$parse['transfer_list_income'] = [];

			$transfers = $this->db->extractResult($this->db->query("SELECT t.*, u.username AS target FROM game_log_transfers t LEFT JOIN game_users u ON u.id = t.user_id WHERE t.target_id = '" . $SelUser['id'] . "' ORDER BY id DESC"));

			foreach ($transfers AS $transfer)
			{
				preg_match("/s\:\[(.*?)\:(.*?)\:(.*?)\((.*?)\)\];e\:\[(.*?)\:(.*?)\:(.*?)\((.*?)\)\];f\:\[(.*?)\];m\:(.*?);c\:(.*?);d\:(.*?);/", $transfer['data'], $t);

				$parse['transfer_list_income'][] = [
					'time' 	=> $transfer['time'],
					'start' => '<a href="/?set=galaxy&r=3&galaxy='.$t[1].'&system='.$t[2].'&planet='.$t[3].'" target="_blank">'.$t[1].':'.$t[2].':'.$t[3].' ('._getText('type_planet', $t[4]).')</a>',
					'end' 	=> '<a href="/?set=galaxy&r=3&galaxy='.$t[5].'&system='.$t[6].'&planet='.$t[7].'" target="_blank">'.$t[5].':'.$t[6].':'.$t[7].' ('._getText('type_planet', $t[8]).')</a>',
					'metal'	=> $t[10],
					'crystal'	=> $t[11],
					'deuterium'	=> $t[12],
					'target'	=> $transfer['target'],
				];
			}

			$parse['history_list'] = $this->db->extractResult($this->db->query("SELECT * FROM game_log_history WHERE user_id = ".$SelUser['id']." AND time > ".(time() - 86400 * 7)." ORDER BY time"));

			$parse['adm_sub_form3'] = "<table class='table'><tr><th colspan=\"4\">" . _getText('adm_technos') . "</th></tr>";

			foreach ($this->registry->reslist['tech'] AS $Item)
			{
				if (isset($this->registry->resource[$Item]))
					$parse['adm_sub_form3'] .= "<tr><td>" . _getText('tech', $Item) . "</td><td>" . $SelUser[$this->registry->resource[$Item]] . "</td></tr>";
			}

			$parse['adm_sub_form3'] .= "</table>";

			$logs = $this->db->query("SELECT ip, time FROM game_log_ip WHERE id = " . $SelUser['id'] . " ORDER BY time DESC");

			$parse['adm_sub_form4'] = "<table class='table'><tr><th colspan=\"2\">Смены IP</th></tr>";

			while ($log = $logs->fetch())
			{
				$parse['adm_sub_form4'] .= "<tr><td>".long2ip($log['ip'])."</td><td>".$this->game->datezone("d.m.Y H:i", $log['time'])."</td></tr>";
			}

			$parse['adm_sub_form4'] .= "</table>";

			$logs_lang = ['', 'WMR', 'Ресурсы', 'Реферал', 'Уровень', 'Офицер', 'Админка', 'Смена фракции'];

			$logs = $this->db->query("SELECT time, credits, type FROM game_log_credits WHERE uid = " . $SelUser['id'] . " ORDER BY time DESC");

			$parse['adm_sub_form4'] .= "<table class='table'><tr><th colspan=\"4\">Кредитная история</th></tr>";

			while ($log = $logs->fetch())
			{
				$parse['adm_sub_form4'] .= "<tr><td width=40%>" . $this->game->datezone("d.m.Y H:i", $log['time']) . "</td>";
				$parse['adm_sub_form4'] .= "<td>" . $log['credits'] . "</td>";
				$parse['adm_sub_form4'] .= "<td width=40%>" . $logs_lang[$log['type']] . "</td></tr>";
			}

			$parse['adm_sub_form4'] .= "</table>";

			$logs = $this->db->query("SELECT time, planet_start, planet_end, fleet, battle_log FROM game_log_attack WHERE uid = " . $SelUser['id'] . " ORDER BY time DESC");

			$parse['adm_sub_form4'] .= "<table class='table'><tr><th colspan=\"4\">Логи атак</th></tr>";

			while ($log = $logs->fetch())
			{
				$parse['adm_sub_form4'] .= "<tr><td width=40%>" . $this->game->datezone("d.m.Y H:i", $log['time']) . "</td>";
				$parse['adm_sub_form4'] .= "<td>S:" . $log['planet_start'] . "</td>";
				$parse['adm_sub_form4'] .= "<td width=30%>E:" . $log['planet_end'] . "</td></tr>";

				$parse['adm_sub_form4'] .= "<tr><td colspan=3><a href=\"".$this->url->get("rw/".$log['battle_log']."/".md5('xnovasuka' . $log['battle_log'])."/")."\" target=\"_blank\">" . $log['fleet'] . "</a></td></tr>";
			}

			$parse['adm_sub_form4'] .= "</table>";

			$logs = $this->db->query("SELECT ip FROM game_log_ip WHERE id = " . $SelUser['id'] . " GROUP BY ip");

			$parse['adm_sub_form5'] = "<table class='table'><tr><th colspan=\"3\">Пересечения по IP</th></tr>";

			while ($log = $logs->fetch())
			{
				$ips = $this->db->query("SELECT u.id, u.username, l.time FROM game_log_ip l LEFT JOIN game_users u ON u.id = l.id WHERE l.ip = " . $log['ip'] . " AND l.id != " . $SelUser['id'] . " GROUP BY l.id;");

				while ($ip = $ips->fetch())
				{
					$parse['adm_sub_form5'] .= "<tr><td width=40%>" . $this->game->datezone("d.m.Y H:i", $ip['time']) . "</td>";
					$parse['adm_sub_form5'] .= "<td>" . long2ip($log['ip']) . "</td>";
					$parse['adm_sub_form5'] .= "<td width=30%><a href='".$this->url->get("admin/manager/data/username/".$ip['id']."/send/")."' target='_blank'>" . $ip['username'] . "</a></td></tr>";
				}
			}

			$parse['adm_sub_form5'] .= "</table>";

			$logs = $this->db->query("SELECT u_id, a_id, text, time FROM game_private WHERE u_id = " . $SelUser['id'] . " ORDER BY time DESC");

			$parse['adm_sub_form5'] .= "<table class='table'><tr><th colspan=\"3\">Записи в личном деле</th></tr>";

			while ($log = $logs->fetch())
			{
				$parse['adm_sub_form5'] .= "<tr><td width=25%>" . $this->game->datezone("d.m.Y H:i", $log['time']) . "</td>";
				$parse['adm_sub_form5'] .= "<td width=20%><a href='/?set=players&id=" . $log['a_id'] . "' target='_blank'>" . $log['a_id'] . "</a></td>";
				$parse['adm_sub_form5'] .= "<td>" . $log['text'] . "</td></tr>";
			}

			$parse['adm_sub_form5'] .= "</table>";

			$this->view->pick('manager/adminpanel_ans1');
			$this->view->setVar('parse', $parse);
		}
	}
}