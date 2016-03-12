<?php
namespace App\Controllers\Admin;

use App\Controllers\AdminController;
use App\Lang;

class PlayerAdmin
{
	public function show (AdminController $controller)
	{
		if ($controller->user->authlevel >= 1)
		{
			Lang::includeLang('admin/adminpanel');

			if ($controller->request->get('result', 'string', '') != '')
			{
				$result = $controller->request->get('result');

				switch ($result)
				{
					case 'usr_data':

						if ($controller->user->authlevel >= 1)
						{
							$username = $controller->request->get('username');

							$SelUser = $controller->db->query("SELECT u.*, ui.* FROM game_users u, game_users_info ui WHERE ui.id = u.id AND ".(is_numeric($username) ? "u.`id` = '" . $username . "'" : "u.`username` = '" . $username . "'")." LIMIT 1;")->fetch();

							if (!isset($SelUser['id']))
								$controller->message('Такого игрока не существует', 'Ошибка', '/admin/paneladmina/', 2);

							$parse = [];
							$parse['answer1'] = $SelUser['id'];
							$parse['answer2'] = $SelUser['username'];
							$parse['answer3'] = long2ip($SelUser['ip']);
							$parse['answer4'] = $SelUser['email'];
							$parse['answer5'] = _getText('user_level', $SelUser['authlevel']);
							$parse['answer6'] = _getText('adm_usr_genre', $SelUser['sex']);
							$parse['answer7'] = date('d.m.Y H:i:s', $SelUser['vacation']);
							$parse['answer9'] = date('d.m.Y H:i:s', $SelUser['create_time']);
							$parse['answer8'] = "[" . $SelUser['galaxy'] . ":" . $SelUser['system'] . ":" . $SelUser['planet'] . "] ";

							$parse['planet_list'] = [];
							$parse['planet_fields'] = $controller->storage->resource;

							if ($controller->user->authlevel > 1)
								$parse['planet_list'] = $controller->db->extractResult($controller->db->query("SELECT * FROM game_planets WHERE `id_owner` = '" . $SelUser['id'] . "' ORDER BY id ASC"));

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

							$transfers = $controller->db->extractResult($controller->db->query("SELECT t.*, u.username AS target FROM game_log_transfers t LEFT JOIN game_users u ON u.id = t.target_id WHERE t.`user_id` = '" . $SelUser['id'] . "' ORDER BY id DESC"));

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

							$transfers = $controller->db->extractResult($controller->db->query("SELECT t.*, u.username AS target FROM game_log_transfers t LEFT JOIN game_users u ON u.id = t.user_id WHERE t.`target_id` = '" . $SelUser['id'] . "' ORDER BY id DESC"));

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

							$parse['history_list'] = $controller->db->extractResult($controller->db->query("SELECT * FROM game_log_history WHERE user_id = ".$SelUser['id']." AND time > ".(time() - 86400 * 7)." ORDER BY time"));

							if ($controller->user->authlevel > 1)
							{
								$parse['adm_sub_form3'] = "<table class='table'><tr><th colspan=\"4\">" . _getText('adm_technos') . "</th></tr>";

								foreach ($controller->storage->reslist['tech'] AS $Item)
								{
									if (isset($controller->storage->resource[$Item]))
										$parse['adm_sub_form3'] .= "<tr><td>" . _getText('tech', $Item) . "</td><td>" . $SelUser[$controller->storage->resource[$Item]] . "</td></tr>";
								}

								$parse['adm_sub_form3'] .= "</table>";
							}

							$logs = $controller->db->query("SELECT ip, time FROM game_log_ip WHERE id = " . $SelUser['id'] . " ORDER BY time DESC");

							$parse['adm_sub_form4'] = "<table class='table'><tr><th colspan=\"2\">Смены IP</th></tr>";

							while ($log = $logs->fetch())
							{
								$parse['adm_sub_form4'] .= "<tr><td>".long2ip($log['ip'])."</td><td>".$controller->game->datezone("d.m.Y H:i", $log['time'])."</td></tr>";
							}

							$parse['adm_sub_form4'] .= "</table>";

							$logs_lang = ['', 'WMR', 'Ресурсы', 'Реферал', 'Уровень', 'Офицер', 'Админка', 'Смена фракции'];

							if ($controller->user->authlevel > 1)
							{
								$logs = $controller->db->query("SELECT time, credits, type FROM game_log_credits WHERE uid = " . $SelUser['id'] . " ORDER BY time DESC");

								$parse['adm_sub_form4'] .= "<table class='table'><tr><th colspan=\"4\">Кредитная история</th></tr>";

								while ($log = $logs->fetch())
								{
									$parse['adm_sub_form4'] .= "<tr><td width=40%>" . $controller->game->datezone("d.m.Y H:i", $log['time']) . "</td>";
									$parse['adm_sub_form4'] .= "<td>" . $log['credits'] . "</td>";
									$parse['adm_sub_form4'] .= "<td width=40%>" . $logs_lang[$log['type']] . "</td></tr>";
								}

								$parse['adm_sub_form4'] .= "</table>";
							}

							$logs = $controller->db->query("SELECT time, planet_start, planet_end, fleet, battle_log FROM game_log_attack WHERE uid = " . $SelUser['id'] . " ORDER BY time DESC");

							$parse['adm_sub_form4'] .= "<table class='table'><tr><th colspan=\"4\">Логи атак</th></tr>";

							while ($log = $logs->fetch())
							{
								$parse['adm_sub_form4'] .= "<tr><td width=40%>" . $controller->game->datezone("d.m.Y H:i", $log['time']) . "</td>";
								$parse['adm_sub_form4'] .= "<td>S:" . $log['planet_start'] . "</td>";
								$parse['adm_sub_form4'] .= "<td width=30%>E:" . $log['planet_end'] . "</td></tr>";

								$parse['adm_sub_form4'] .= "<tr><td colspan=3><a href=\"/?set=rw&r=" . $log['battle_log'] . "&amp;k=" . md5('xnovasuka' . $log['battle_log']) . "\" target=\"_blank\">" . $log['fleet'] . "</a></td></tr>";
							}

							$parse['adm_sub_form4'] .= "</table>";

							$logs = $controller->db->query("SELECT ip FROM game_log_ip WHERE id = " . $SelUser['id'] . " GROUP BY ip");

							$parse['adm_sub_form5'] = "<table class='table'><tr><th colspan=\"3\">Пересечения по IP</th></tr>";

							while ($log = $logs->fetch())
							{
								$ips = $controller->db->query("SELECT u.id, u.username, l.time FROM game_log_ip l LEFT JOIN game_users u ON u.id = l.id WHERE l.ip = " . $log['ip'] . " AND l.id != " . $SelUser['id'] . " GROUP BY l.id;");

								while ($ip = $ips->fetch())
								{
									$parse['adm_sub_form5'] .= "<tr><td width=40%>" . $controller->game->datezone("d.m.Y H:i", $ip['time']) . "</td>";
									$parse['adm_sub_form5'] .= "<td>" . long2ip($log['ip']) . "</td>";
									$parse['adm_sub_form5'] .= "<td width=30%><a href='/?set=players&id=" . $ip['id'] . "' target='_blank'>" . $ip['username'] . "</a></td></tr>";
								}
							}

							$parse['adm_sub_form5'] .= "</table>";

							$logs = $controller->db->query("SELECT u_id, a_id, text, time FROM game_private WHERE u_id = " . $SelUser['id'] . " ORDER BY time DESC");

							$parse['adm_sub_form5'] .= "<table class='table'><tr><th colspan=\"3\">Записи в личном деле</th></tr>";

							while ($log = $logs->fetch())
							{
								$parse['adm_sub_form5'] .= "<tr><td width=25%>" . $controller->game->datezone("d.m.Y H:i", $log['time']) . "</td>";
								$parse['adm_sub_form5'] .= "<td width=20%><a href='/?set=players&id=" . $log['a_id'] . "' target='_blank'>" . $log['a_id'] . "</a></td>";
								$parse['adm_sub_form5'] .= "<td>" . $log['text'] . "</td></tr>";
							}

							$parse['adm_sub_form5'] .= "</table>";

							$controller->view->pick('admin/adminpanel_ans1');
							$controller->view->setVar('parse', $parse);

						}

						break;

					case 'usr_level':

						if ($controller->user->authlevel >= 3)
						{

							$Player = addslashes($_POST['player']);
							$NewLvl = intval($_POST['authlvl']);

							$controller->db->query("UPDATE game_users SET `authlevel` = '" . $NewLvl . "' WHERE `username` = '" . $Player . "';");

							$Message = _getText('adm_mess_lvl1') . " " . $Player . " " . _getText('adm_mess_lvl2');
							$Message .= "<font color=\"red\">" . _getText('adm_usr_level', $NewLvl) . "</font>!";

							$controller->message($Message, _getText('adm_mod_level'));

						}
						break;

					case 'ip_search':
						$Pattern = addslashes($_POST['ip']);
						$SelUser = $controller->db->query("SELECT * FROM game_users WHERE `ip` = INET_ATON('" . $Pattern . "');");
						$parse = [];
						$parse['adm_this_ip'] = $Pattern;
						$parse['adm_plyer_lst'] = '';
						while ($Usr = $SelUser->fetch())
						{
							$UsrMain = $controller->db->query("SELECT `name` FROM game_planets WHERE `id` = '" . $Usr['planet_id'] . "';")->fetch();
							$parse['adm_plyer_lst'] .= "<tr><th>" . $Usr['username'] . "</th><th>[" . $Usr['galaxy'] . ":" . $Usr['system'] . ":" . $Usr['planet'] . "] " . $UsrMain['name'] . "</th></tr>";
						}
						$controller->view->pick('admin/adminpanel_ans2');
						$controller->view->setVar('parse', $parse);
						break;
					default:
						break;
				}
			}
			elseif ($controller->request->get('mode', 'string', '') != '')
			{
				switch ($controller->request->get('mode', 'string', ''))
				{
					case 'usr_data':

						if ($controller->user->authlevel >= 1)
							$controller->view->pick('admin/adminpanel_f4');
						else
							$controller->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));

						break;

					case 'usr_level':

						if ($controller->user->authlevel >= 3)
							$controller->view->pick('admin/adminpanel_f3');
						else
							$controller->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));

						break;

					case 'ip_search':

						$controller->view->pick('admin/adminpanel_f2');

						break;
				}
			}
			else
				$controller->view->pick('admin/adminpanel');

			$controller->tag->setTitle(_getText('panel_mainttl'));
		}
		else
			$controller->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));
	}
}

?>