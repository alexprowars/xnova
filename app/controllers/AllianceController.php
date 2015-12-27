<?php

namespace App\Controllers;

use Xcms\db;
use Xcms\request;
use Xcms\sql;
use Xcms\strings;
use Xnova\User;
use Xnova\pageHelper;

class AllianceController extends ApplicationController
{
	function __construct ()
	{
		parent::__construct();

		strings::includeLang('alliance');
	}
	
	public function show ()
	{
		if (!user::get()->isAuthorized())
			$this->message(_getText('Denied_access'), "Ошипко");
	
		if (user::get()->data['ally_id'] == 0)
		{
			if (isset($_POST['bcancel']) && isset($_POST['r_id']))
			{
				db::query("DELETE FROM game_alliance_requests WHERE a_id = " . intval($_POST['r_id']) . " AND `u_id` = " . user::get()->data['id']);

				$this->message("Вы отозвали свою заявку на вступление в альянс", "Отзыв заявки", "?set=alliance", 2);
			}

			$parse = array();

			$parse['list'] = array();

			$requests = db::query("SELECT r.*, a.ally_name, a.ally_tag FROM game_alliance_requests r LEFT JOIN game_alliance a ON a.id = r.a_id WHERE r.u_id = " . user::get()->data['id'] . ";");

			while ($request = db::fetch_assoc($requests))
				$parse['list'][] = array($request['a_id'], $request['ally_tag'], $request['ally_name'], $request['time']);

			$parse['allys'] = array();

			$allys = db::query("SELECT s.total_points, a.`id`, a.`ally_tag`, a.`ally_name`, a.`ally_members` FROM game_statpoints s, game_alliance a WHERE s.`stat_type` = '2' AND s.`stat_code` = '1' AND a.id = s.id_owner ORDER BY s.`total_points` DESC LIMIT 0,15;");

			while ($ally = db::fetch_assoc($allys))
			{
				$ally['total_points'] = strings::pretty_number($ally['total_points']);
				$parse['allys'][] = $ally;
			}

			$this->setTemplate('alliance/alliance_default');
			$this->set('parse', $parse);

			$this->setTitle(_getText('alliance'));
			$this->showTopPanel(false);
			$this->display();
		}
		else
		{
			$sort1 = (isset($_GET['sort1'])) ? intval($_GET['sort1']) : 0;
			$sort2 = (isset($_GET['sort2'])) ? intval($_GET['sort2']) : 0;
			$rank = (isset($_GET['rank'])) ? intval($_GET['rank']) : 0;

			$d = @$_GET['d'];
			if ((!is_numeric($d)) || (empty($d) && $d != 0))
				unset($d);

			$kick = @intval($_GET['kick']);
			if (empty($kick))
				unset($kick);

			$id = @intval($_GET['id']);
			if (empty($id))
				unset($id);

			$mode = @$_GET['mode'];
			$edit = @$_GET['edit'];
			$show = @intval($_GET['show']);
			$t = @$_GET['t'];

			$ally = db::query("SELECT * FROM game_alliance WHERE id = '" . user::get()->data['ally_id'] . "'", true);
	
			$ally_member = db::query("SELECT * FROM game_alliance_members WHERE u_id = " . user::get()->data['id'] . ";", true);
	
			if ($ally_member['a_id'] != $ally['id'])
				db::query("DELETE FROM game_alliance_members WHERE u_id = " . user::get()->data['id'] . ";");
	
			if ($ally['ally_ranks'] == NULL)
				$ally['ally_ranks'] = 'a:0:{}';
	
			$ally_ranks = json_decode($ally['ally_ranks'], true);
	
			if ($ally['ally_owner'] == user::get()->data['id'])
			{
				$user_can_watch_memberlist_status = true;
				$user_can_watch_memberlist = true;
				$user_can_send_mails = true;
				$user_can_kick = true;
				$user_can_edit_rights = true;
				$user_can_exit_alliance = true;
				$user_bewerbungen_bearbeiten = true;
				$user_admin = true;
				$user_diplomacy = true;
			}
			elseif ($ally_member['rank'] == 0)
			{
				$user_can_watch_memberlist_status = false;
				$user_can_watch_memberlist = false;
				$user_can_send_mails = false;
				$user_can_kick = false;
				$user_can_edit_rights = false;
				$user_can_exit_alliance = false;
				$user_bewerbungen_bearbeiten = false;
				$user_admin = false;
				$user_diplomacy = false;
			}
			else
			{
				$user_can_watch_memberlist_status = ($ally_ranks[$ally_member['rank'] - 1]['onlinestatus'] == 1) ? true : false;
				$user_can_watch_memberlist = ($ally_ranks[$ally_member['rank'] - 1]['memberlist'] == 1) ? true : false;
				$user_can_send_mails = ($ally_ranks[$ally_member['rank'] - 1]['mails'] == 1) ? true : false;
				$user_can_kick = ($ally_ranks[$ally_member['rank'] - 1]['kick'] == 1) ? true : false;
				$user_can_edit_rights = ($ally_ranks[$ally_member['rank'] - 1]['rechtehand'] == 1) ? true : false;
				$user_can_exit_alliance = ($ally_ranks[$ally_member['rank'] - 1]['delete'] == 1) ? true : false;
				$user_bewerbungen_bearbeiten = ($ally_ranks[$ally_member['rank'] - 1]['bewerbungenbearbeiten'] == 1) ? true : false;
				$user_admin = ($ally_ranks[$ally_member['rank'] - 1]['administrieren'] == 1) ? true : false;
				$user_diplomacy = ($ally_ranks[$ally_member['rank'] - 1]['diplomacy'] == 1) ? true : false;
			}
	
			if (!isset($ally['id']))
			{
				db::query("UPDATE game_users SET `ally_id` = 0 WHERE `id` = '" . user::get()->data['id'] . "'");
				db::query("DELETE FROM game_alliance_members WHERE u_id = " . user::get()->data['id'] . ";");
	
				$this->message(_getText('ally_notexist'), _getText('your_alliance'), '?set=alliance');
			}
	
			if (!isset($ally_member['a_id']))
			{
				db::query("INSERT INTO game_alliance_members (a_id, u_id, time) VALUES (" . $ally['id'] . ", " . user::get()->data['id'] . ", " . time() . ")");
	
				request::redirectTo("?set=alliance");
			}
	
			if ($mode == 'exit')
			{
				if ($ally['ally_owner'] == user::get()->data['id'])
					$this->message(_getText('Owner_cant_go_out'), _getText('Alliance'));
	
				if (isset($_GET['yes']))
				{
					db::query("UPDATE game_planets SET id_ally = 0 WHERE id_owner = ".user::get()->data['id']." AND id_ally = ".$ally['id']."");
	
					db::query("UPDATE game_users SET `ally_id` = 0, `ally_name` = '' WHERE `id` = '" . user::get()->data['id'] . "'");
					db::query("DELETE FROM game_alliance_members WHERE u_id = " . user::get()->data['id'] . ";");
	
					$html = $this->MessageForm(_getText('Go_out_welldone'), "<br>", '?set=alliance', _getText('Ok'));
				}
				else
					$html = $this->MessageForm(_getText('Want_go_out'), "<br>", "?set=alliance&mode=exit&yes=1", "Подтвердить");

				$this->setTitle('Выход их альянса');
				$this->setContent($html);
				$this->showTopPanel(false);
				$this->display();
			}
	
			if ($mode == 'diplo')
			{
				if ($ally['ally_owner'] != user::get()->data['id'] && !$user_diplomacy)
					$this->message(_getText('Denied_access'), "Дипломатия");
	
				$parse['DText'] = "";
				$parse['DMyQuery'] = "";
				$parse['DQuery'] = "";
	
				$status = array(0 => "Нейтральное", 1 => "Перемирие", 2 => "Мир", 3 => "Война");
	
				if (isset($_GET['edit']) && $_GET['edit'] == "add")
				{
					$st = intval($_POST['status']);
					$al = db::query("SELECT id, ally_name FROM game_alliance WHERE id = '" . intval($_POST['ally']) . "'", true);
					if (!$al['id'])
						$this->message("Ошибка ввода параметров", "Дипломатия", "?set=alliance&mode=diplo", 3);
	
					$ad = db::query("SELECT id FROM game_alliance_diplomacy WHERE a_id = " . $ally['id'] . " AND d_id = " . $al['id'] . ";");
					if (db::num_rows($ad) > 0)
						$this->message("У вас уже есть соглашение с этим альянсом. Разорвите старое соглашения прежде чем создать новое.", "Дипломатия", "?set=alliance&mode=diplo", 3);
	
					if ($st < 0 || $st > 3)
						$st = 0;
	
					db::query("INSERT INTO game_alliance_diplomacy VALUES (NULL, " . $ally['id'] . ", " . $al['id'] . ", " . $st . ", 0, 1)");
					db::query("INSERT INTO game_alliance_diplomacy VALUES (NULL, " . $al['id'] . ", " . $ally['id'] . ", " . $st . ", 0, 0)");
	
					$this->message("Отношение между вашими альянсами успешно добавлено", "Дипломатия", "?set=alliance&mode=diplo", 3);
	
				}
				elseif (isset($_GET['edit']) && $_GET['edit'] == "del")
				{
					$al = db::query("SELECT a_id, d_id FROM game_alliance_diplomacy WHERE id = '" . intval($_GET['id']) . "' AND a_id = " . $ally['id'] . ";", true);
	
					if (!$al['a_id'])
						$this->message("Ошибка ввода параметров", "Дипломатия", "?set=alliance&mode=diplo", 3);
	
					db::query("DELETE FROM game_alliance_diplomacy WHERE a_id = " . $al['a_id'] . " AND d_id = " . $al['d_id'] . ";");
					db::query("DELETE FROM game_alliance_diplomacy WHERE a_id = " . $al['d_id'] . " AND d_id = " . $al['a_id'] . ";");
	
					$this->message("Отношение между вашими альянсами расторжено", "Дипломатия", "?set=alliance&mode=diplo", 3);
	
				}
				elseif (isset($_GET['edit']) && $_GET['edit'] == "suc")
				{
					$al = db::query("SELECT a_id, d_id FROM game_alliance_diplomacy WHERE id = '" . intval($_GET['id']) . "' AND a_id = " . $ally['id'] . "", true);
	
					if (!$al['a_id'])
						$this->message("Ошибка ввода параметров", "Дипломатия", "?set=alliance&mode=diplo", 3);
	
					db::query("UPDATE game_alliance_diplomacy SET status = 1 WHERE a_id = " . $al['a_id'] . " AND d_id = " . $al['d_id'] . ";");
					db::query("UPDATE game_alliance_diplomacy SET status = 1 WHERE a_id = " . $al['d_id'] . " AND d_id = " . $al['a_id'] . ";");
	
					$this->message("Отношение между вашими альянсами подтверждено", "Дипломатия", "?set=alliance&mode=diplo", 3);
				}
	
				$dp = db::query("SELECT ad.*, a.ally_name FROM game_alliance_diplomacy ad, game_alliance a WHERE a.id = ad.d_id AND ad.a_id = '" . $ally['id'] . "';");
	
				while ($diplo = db::fetch_assoc($dp))
				{
					if ($diplo['status'] == 0)
					{
						if ($diplo['primary'] == 1)
							$parse['DMyQuery'] .= "<tr><th>" . $diplo['ally_name'] . "</th><th>" . $status[$diplo['type']] . "</th><th><a href=\"?set=alliance&mode=diplo&edit=del&id={$diplo['id']}\"><img src=\"".DPATH."pic/abort.gif\" alt=\"Удалить заявку\"></a></th></tr>";
						else
							$parse['DQuery'] .= "<tr><th>" . $diplo['ally_name'] . "</th><th>" . $status[$diplo['type']] . "</th><th><a href=\"?set=alliance&mode=diplo&edit=suc&id={$diplo['id']}\"><img src=\"".DPATH."pic/appwiz.gif\" alt=\"Подтвердить\"></a> <a href=\"?set=alliance&mode=diplo&edit=del&id={$diplo['id']}\"><img src=\"".DPATH."pic/abort.gif\" alt=\"Удалить заявку\"></a></th></tr>";
					}
					else
						$parse['DText'] .= "<tr><th>" . $diplo['ally_name'] . "</th><th>" . $ally['ally_name'] . "</th><th>" . $status[$diplo['type']] . "</th><th><a href=\"?set=alliance&mode=diplo&edit=del&id=".$diplo['id']."\"><img src=\"".DPATH."pic/abort.gif\" alt=\"Удалить\"></a></th></tr>";
				}
	
				if ($parse['DMyQuery'] == "")
					$parse['DMyQuery'] = "<tr><th colspan=3>нет</th></tr>";
				if ($parse['DQuery'] == "")
					$parse['DQuery'] = "<tr><th colspan=3>нет</th></tr>";
				if ($parse['DText'] == "")
					$parse['DText'] = "<tr><th colspan=4>нет</th></tr>";
	
				$parse['a_list'] = "<option value=\"0\">список альянсов";
	
				$ally_list = db::query("SELECT id, ally_name, ally_tag FROM game_alliance WHERE id != " . user::get()->data['ally_id'] . " AND ally_members > 0");
	
				while ($a_list = db::fetch_assoc($ally_list))
					$parse['a_list'] .= "<option value=\"" . $a_list['id'] . "\">" . $a_list['ally_name'] . " [" . $a_list['ally_tag'] . "]";
	
				$this->setTemplate('alliance/alliance_diplomacy');
				$this->set('parse', $parse);

				$this->setTitle('Дипломатия');
				$this->showTopPanel(false);
				$this->display();
			}
	
			if ($mode == 'memberslist' || ($mode == 'admin' && $edit == 'members'))
			{
				$parse = array();
	
				if ($mode == 'admin' && $edit == 'members')
				{
					if ($ally['ally_owner'] != user::get()->data['id'] && !$user_can_kick)
						$this->message(_getText('Denied_access'), _getText('Members_list'));
	
					if (isset($kick))
					{
						if ($ally['ally_owner'] != user::get()->data['id'] && !$user_can_kick)
							$this->message(_getText('Denied_access'), _getText('Members_list'));
	
						$u = db::query("SELECT * FROM game_users WHERE id = '" . $kick . "' LIMIT 1", true);
	
						if ($u['ally_id'] == $ally['id'] && $u['id'] != $ally['ally_owner'])
						{
							db::query("UPDATE game_planets SET id_ally = 0 WHERE id_owner = ".$u['id']." AND id_ally = ".$ally['id']."");
	
							db::query("UPDATE game_users SET `ally_id` = '0', `ally_name` = '' WHERE `id` = '" . $u['id'] . "'");
							db::query("DELETE FROM game_alliance_members WHERE u_id = " . $u['id'] . ";");
						}
					}
					elseif (request::P('newrang', '') != '' && request::R('id', 0, VALUE_INT) != 0)
					{
						$id = request::R('id', 0, VALUE_INT);
						$rank = request::P('newrang', 0, VALUE_INT);

						$q = db::query("SELECT `id`, `ally_id` FROM game_users WHERE id = '" . $id . "' LIMIT 1", true);
	
						if ((isset($ally_ranks[$rank - 1]) || $rank == 0) && $q['id'] != $ally['ally_owner'] && $q['ally_id'] == $ally['id'])
							db::query("UPDATE game_alliance_members SET `rank` = '" . $rank . "' WHERE `u_id` = '" . $id . "';");
					}
	
					$parse['admin'] = true;
				}
				else
				{
					if ($ally['ally_owner'] != user::get()->data['id'] && !$user_can_watch_memberlist)
						$this->message(_getText('Denied_access'), _getText('Members_list'));
	
					$parse['admin'] = false;
				}
	
				$sort = "";
	
				if ($sort2)
				{
					if ($sort1 == 1)
						$sort = " ORDER BY u.`username`";
					elseif ($sort1 == 2)
						$sort = " ORDER BY m.`rank`";
					elseif ($sort1 == 3)
						$sort = " ORDER BY s.`total_points`";
					elseif ($sort1 == 4)
						$sort = " ORDER BY m.`time`";
					elseif ($sort1 == 5 && $user_can_watch_memberlist_status)
						$sort = " ORDER BY u.`onlinetime`";
					else
						$sort = " ORDER BY u.`id`";
	
					if ($sort2 == 1)
						$sort .= " DESC;";
					elseif ($sort2 == 2)
						$sort .= " ASC;";
				}
				$listuser = db::query("SELECT u.id, u.username, u.race, u.galaxy, u.system, u.planet, u.onlinetime, m.rank, m.time, s.total_points FROM game_users u LEFT JOIN game_alliance_members m ON m.u_id = u.id LEFT JOIN game_statpoints s ON s.id_owner = u.id AND stat_type = 1 WHERE u.ally_id = '" . user::get()->data['ally_id'] . "'" . $sort . "");
	
				$i = 0;
				$parse['memberslist'] = array();
	
				while ($u = db::fetch_assoc($listuser))
				{
					$i++;
					$u['i'] = $i;
	
					if ($u["onlinetime"] + 60 * 10 >= time() && $user_can_watch_memberlist_status)
						$u["onlinetime"] = "lime>" . _getText('On') . "<";
					elseif ($u["onlinetime"] + 60 * 20 >= time() && $user_can_watch_memberlist_status)
						$u["onlinetime"] = "yellow>" . _getText('15_min') . "<";
					elseif ($user_can_watch_memberlist_status)
					{
						$hours = floor((time() - $u["onlinetime"]) / 3600);
						$u["onlinetime"] = "red>" . _getText('Off') . " " . floor($hours / 24) . " д. " . ($hours % 24) . " ч.<";
					}
	
					if ($ally['ally_owner'] == $u['id'])
						$u["ally_range"] = ($ally['ally_owner_range'] == '') ? "Основатель" : $ally['ally_owner_range'];
					elseif (isset($ally_ranks[$u['rank'] - 1]['name']))
						$u["ally_range"] = $ally_ranks[$u['rank'] - 1]['name'];
					else
						$u["ally_range"] = _getText('Novate');
	
					$u['points'] = strings::pretty_number($u['total_points']);
					$u['time'] = ($u['time'] > 0) ? datezone("d.m.Y H:i", $u['time']) : '-';
	
					$parse['memberslist'][] = $u;
	
					if ($rank == $u['id'] && $parse['admin'])
					{
						$r['Rank_for'] = 'Установить ранг для ' . $u['username'];
						$r['options'] = "<option value=\"0\">Новичок</option>";
	
						if (is_array($ally_ranks) && count($ally_ranks))
						{
							foreach ($ally_ranks as $a => $b)
							{
								$r['options'] .= "<option value=\"" . ($a + 1) . "\"";
	
								if ($u['rank'] - 1 == $a)
									$r['options'] .= ' selected=selected';
	
								$r['options'] .= ">" . $b['name'] . "</option>";
							}
						}
	
						$r['id'] = $u['id'];
	
						$parse['memberslist'][] = $r;
					}
				}
	
				if ($sort2 == 1)
					$s = 2;
				elseif ($sort2 == 2)
					$s = 1;
				else
					$s = 1;
	
				if ($i != $ally['ally_members'])
					db::query("UPDATE game_alliance SET `ally_members` = '" . $i . "' WHERE `id` = '" . $ally['id'] . "'");
	
				$parse['i'] = $i;
				$parse['s'] = $s;
				$parse['status'] = $user_can_watch_memberlist_status;
	
				$this->setTemplate('alliance/alliance_members_admin');
				$this->set('parse', $parse);

				$this->setTitle(_getText('Members_list'));
				$this->showTopPanel(false);
				$this->display();
			}
	
			if ($mode == 'circular')
			{
				if (user::get()->data['mnl_alliance'] != 0)
					db::query("UPDATE game_users SET `mnl_alliance` = '0' WHERE `id` = '" . user::get()->data['id'] . "'");
	
				if ($ally['ally_owner'] != user::get()->data['id'] && !$user_can_send_mails)
					$this->message(_getText('Denied_access'), _getText('Send_circular_mail'));
	
				if (isset($_POST['deletemessages']) && $ally['ally_owner'] == user::get()->data['id'])
				{
					$DeleteWhat = $_POST['deletemessages'];
	
					if ($DeleteWhat == 'deleteall')
						db::query("DELETE FROM game_alliance_chat WHERE `ally_id` = '" . user::get()->data['ally_id'] . "';");
					elseif ($DeleteWhat == 'deletemarked' || $DeleteWhat == 'deleteunmarked')
					{
						$Mess_Array = array();
	
						foreach ($_POST as $Message => $Answer)
						{
							if (preg_match("/delmes/iu", $Message) && $Answer == 'on')
							{
								$MessId = str_replace("delmes", "", $Message);
								$Mess_Array[] = $MessId;
							}
						}
	
						$Mess_Array = implode(',', $Mess_Array);
	
						if ($Mess_Array != '')
							db::query("DELETE FROM game_alliance_chat WHERE `id` " . (($DeleteWhat == 'deleteunmarked') ? 'NOT' : '') . " IN (" . $Mess_Array . ") AND `ally_id` = '" . user::get()->data['ally_id'] . "';");
					}
				}
	
				if (isset($_GET['sendmail']) && isset($_POST['text']) && $_POST['text'] != '')
				{
					$_POST['text'] = strings::FormatText($_POST['text']);
	
					db::query("INSERT INTO game_alliance_chat SET `ally_id` = '" . user::get()->data['ally_id'] . "', `user` = '" . user::get()->data['username'] . "', user_id = " . user::get()->data['id'] . ", `message` = '" . db::escape_string($_POST['text']) . "', `timestamp` = '" . time() . "'");
					db::query("UPDATE game_users SET `mnl_alliance` = `mnl_alliance` + '1' WHERE `ally_id` = '" . user::get()->data['ally_id'] . "' AND id != " . user::get()->data['id'] . "");
				}
	
				$parse = array();
				$parse['messages'] = array();
	
				$news_count = db::query("SELECT COUNT(*) AS num FROM game_alliance_chat WHERE `ally_id` = '" . user::get()->data['ally_id'] . "'", true);
	
				if ($news_count['num'] > 0)
				{
					$p = (isset($_GET['p'])) ? intval($_GET['p']) : 1;
	
					$thiss = strings::pagination($news_count['num'], 20, '?set=alliance&mode=circular', $p);
	
					$mess = db::query("SELECT * FROM game_alliance_chat WHERE `ally_id` = '" . user::get()->data['ally_id'] . "' ORDER BY `id` DESC limit " . (($p - 1) * 20) . ", 20");
	
					while ($mes = db::fetch_assoc($mess))
					{
						$parse['messages'][] = $mes;
					}
				}
	
				$parse['ally_owner'] = ($ally['ally_owner'] == user::get()->data['id']) ? true : false;
				$parse['pages'] = (isset($thiss)) ? $thiss : '[0]';
				$parse['parser'] = user::get()->getUserOption('bb_parser') ? true : false;
	
				$this->setTemplate('alliance/alliance_chat');
				$this->set('parse', $parse);

				$this->setTitle('Альянс-чат');
				$this->showTopPanel(false);
				$this->display();
			}
	
			if ($mode == 'admin')
			{
				if (user::get()->isAdmin() && $edit == 'planets')
				{
					if ($ally['ally_owner'] != user::get()->data['id'])
						$this->message(_getText('Denied_access'), "Управление планетами");
	
					$parse = array();
	
					$parse['list'] = db::extractResult(db::query("SELECT id, id_ally, name, galaxy, system, planet FROM game_planets WHERE planet_type = 5 AND id_owner = ".user::get()->data['id'].""));
					$parse['credits'] = user::get()->data['credits'];
	
					$parse['bases'] = db::first(db::query("SELECT COUNT(*) AS num FROM game_planets WHERE planet_type = 5 AND id_ally = ".$ally['id']."", true));
	
					$parse['need'] = 100 * ($parse['bases'] > 0 ? (5 + ($parse['bases'] - 1) * 5) : 1);
	
					if (isset($_GET['ally']))
					{
						$id = intval($_GET['ally']);
	
						$check = db::query("SELECT id, id_ally FROM game_planets WHERE planet_type = 5 AND id_ally = 0 AND id_owner = ".user::get()->data['id']." AND id = ".$id."", true);
	
						if (isset($check['id']))
						{
							if (user::get()->data['credits'] >= $parse['need'])
							{
								sql::build()->update('game_planets')->
										setField('id_ally', $ally['id'])->
										setField('name', $ally['ally_name'])
								->where('id', '=', $check['id'])->execute();
	
								sql::build()->update('game_users')->setField('-credits', $parse['need'])->where('id', '=', user::get()->data['id'])->execute();
	
								$this->message("Планета была успешно преобразована", "Управление планетами", "?set=alliance&mode=admin&edit=planets", 3);
							}
							else
								$this->message("Недостаточно кредитов для преобразования планеты", "Управление планетами");
						}
					}
	
					$this->setTemplate('alliance/alliance_planets');
					$this->set('parse', $parse);

					$this->setTitle('Управление планетами');
					$this->showTopPanel(false);
					$this->display();
				}
	
				if ($edit == 'rights')
				{
					if ($ally['ally_owner'] != user::get()->data['id'] && !$user_can_edit_rights && user::get()->data['id'] != 1)
						$this->message(_getText('Denied_access'), _getText('Members_list'));
					elseif (!empty($_POST['newrangname']))
					{
						$name = db::escape_string(strip_tags($_POST['newrangname']));
	
						$ally_ranks[] = array('name' => $name, 'mails' => 0, 'delete' => 0, 'kick' => 0, 'bewerbungen' => 0, 'administrieren' => 0, 'bewerbungenbearbeiten' => 0, 'memberlist' => 0, 'onlinestatus' => 0, 'rechtehand' => 0, 'diplomacy' => 0, 'planet' => 0);
	
						db::query("UPDATE game_alliance SET `ally_ranks` = '" . addslashes(json_encode($ally_ranks)) . "' WHERE `id` = " . $ally['id']);
					}
					elseif (isset($_POST['id']) && is_array($_POST['id']))
					{
						$ally_ranks_new = array();
	
						foreach ($_POST['id'] as $id)
						{
							$name = $ally_ranks[$id]['name'];
	
							$ally_ranks_new[$id]['name'] = $name;
	
							$ally_ranks_new[$id]['delete'] = ($ally['ally_owner'] == user::get()->data['id'] ? (isset($_POST['u' . $id . 'r0']) ? 1 : 0) : $ally_ranks[$id]['delete']);
							$ally_ranks_new[$id]['kick'] = ($ally['ally_owner'] == user::get()->data['id'] ? (isset($_POST['u' . $id . 'r1']) ? 1 : 0) : $ally_ranks[$id]['kick']);
							$ally_ranks_new[$id]['bewerbungen'] = (isset($_POST['u' . $id . 'r2'])) ? 1 : 0;
							$ally_ranks_new[$id]['memberlist'] = (isset($_POST['u' . $id . 'r3'])) ? 1 : 0;
							$ally_ranks_new[$id]['bewerbungenbearbeiten'] = (isset($_POST['u' . $id . 'r4'])) ? 1 : 0;
							$ally_ranks_new[$id]['administrieren'] = (isset($_POST['u' . $id . 'r5'])) ? 1 : 0;
							$ally_ranks_new[$id]['onlinestatus'] = (isset($_POST['u' . $id . 'r6'])) ? 1 : 0;
							$ally_ranks_new[$id]['mails'] = (isset($_POST['u' . $id . 'r7'])) ? 1 : 0;
							$ally_ranks_new[$id]['rechtehand'] = (isset($_POST['u' . $id . 'r8'])) ? 1 : 0;
							$ally_ranks_new[$id]['diplomacy'] = (isset($_POST['u' . $id . 'r9'])) ? 1 : 0;
							$ally_ranks_new[$id]['planet'] = (isset($_POST['u' . $id . 'r10'])) ? 1 : 0;
						}
	
						$ally_ranks = $ally_ranks_new;
	
						db::query("UPDATE game_alliance SET `ally_ranks` = '" . addslashes(json_encode($ally_ranks)) . "' WHERE `id` = " . $ally['id']);
					}
					elseif (isset($d) && isset($ally_ranks[$d]))
					{
						unset($ally_ranks[$d]);
						$ally['ally_rank'] = json_encode($ally_ranks);
	
						db::query("UPDATE game_alliance SET `ally_ranks` = '" . addslashes($ally['ally_rank']) . "' WHERE `id` = " . $ally['id']);
					}
	
					$parse['list'] = array();
	
					if (count($ally_ranks) > 0)
					{
						foreach ($ally_ranks as $a => $b)
						{
							$list['id'] = $a;
							$list['delete'] = "<a href=\"?set=alliance&mode=admin&edit=rights&d={$a}\"><img src=\"" . DPATH . "pic/abort.gif\" alt=\"Удалить ранг\" border=0></a>";
							$list['r0'] = $b['name'];
							$list['a'] = $a;
	
							if ($ally['ally_owner'] == user::get()->data['id'])
								$list['r1'] = "<input type=checkbox name=\"u{$a}r0\"" . (($b['delete'] == 1) ? ' checked="checked"' : '') . ">";
							else
								$list['r1'] = "<b>" . (($b['delete'] == 1) ? '+' : '-') . "</b>";
	
							if ($ally['ally_owner'] == user::get()->data['id'])
								$list['r2'] = "<input type=checkbox name=\"u{$a}r1\"" . (($b['kick'] == 1) ? ' checked="checked"' : '') . ">";
							else
								$list['r2'] = "<b>" . (($b['kick'] == 1) ? '+' : '-') . "</b>";
	
							$list['r3'] = "<input type=checkbox name=\"u{$a}r2\"" . (($b['bewerbungen'] == 1) ? ' checked="checked"' : '') . ">";
							$list['r4'] = "<input type=checkbox name=\"u{$a}r3\"" . (($b['memberlist'] == 1) ? ' checked="checked"' : '') . ">";
							$list['r5'] = "<input type=checkbox name=\"u{$a}r4\"" . (($b['bewerbungenbearbeiten'] == 1) ? ' checked="checked"' : '') . ">";
							$list['r6'] = "<input type=checkbox name=\"u{$a}r5\"" . (($b['administrieren'] == 1) ? ' checked="checked"' : '') . ">";
							$list['r7'] = "<input type=checkbox name=\"u{$a}r6\"" . (($b['onlinestatus'] == 1) ? ' checked="checked"' : '') . ">";
							$list['r8'] = "<input type=checkbox name=\"u{$a}r7\"" . (($b['mails'] == 1) ? ' checked="checked"' : '') . ">";
							$list['r9'] = "<input type=checkbox name=\"u{$a}r8\"" . (($b['rechtehand'] == 1) ? ' checked="checked"' : '') . ">";
							$list['r10'] = "<input type=checkbox name=\"u{$a}r9\"" . (($b['diplomacy'] == 1) ? ' checked="checked"' : '') . ">";
							$list['r11'] = "<input type=checkbox name=\"u{$a}r10\"" . (($b['planet'] == 1) ? ' checked="checked"' : '') . ">";
	
							$parse['list'][] = $list;
						}
					}
	
					$this->setTemplate('alliance/alliance_laws');
					$this->set('parse', $parse);

					$this->setTitle(_getText('Law_settings'));
					$this->showTopPanel(false);
					$this->display();
				}
				elseif ($edit == 'ally')
				{
					if ($ally['ally_owner'] != user::get()->data['id'] && !$user_admin)
						$this->message(_getText('Denied_access'), "Меню управления альянсом");
	
					if ($t != 1 && $t != 2 && $t != 3)
						$t = 1;
	
					if (isset($_POST['options']))
					{
						$ally['ally_owner_range'] = db::escape_string(htmlspecialchars(strip_tags($_POST['owner_range'])));
						$ally['ally_web'] = db::escape_string(htmlspecialchars(strip_tags($_POST['web'])));
						$ally['ally_image'] = db::escape_string(htmlspecialchars(strip_tags($_POST['image'])));
						$ally['ally_request_notallow'] = intval($_POST['request_notallow']);
	
						if ($ally['ally_request_notallow'] != 0 && $ally['ally_request_notallow'] != 1)
							$this->message("Недопустимое значение атрибута!", "Ошибка");
	
						db::query("UPDATE game_alliance SET `ally_owner_range`='" . $ally['ally_owner_range'] . "', `ally_image`='" . $ally['ally_image'] . "', `ally_web`='" . $ally['ally_web'] . "', `ally_request_notallow`='" . $ally['ally_request_notallow'] . "' WHERE `id`='" . $ally['id'] . "'");
					}
					elseif (isset($_POST['t']))
					{
						if ($t == 3)
						{
							$ally['ally_request'] = strings::FormatText($_POST['text']);
							db::query("UPDATE game_alliance SET `ally_request`='" . $ally['ally_request'] . "' WHERE `id`='" . $ally['id'] . "'");
						}
						elseif ($t == 2)
						{
							$ally['ally_text'] = strings::FormatText($_POST['text']);
							db::query("UPDATE game_alliance SET `ally_text`='" . $ally['ally_text'] . "' WHERE `id`='" . $ally['id'] . "'");
						}
						else
						{
							$ally['ally_description'] = strings::FormatText($_POST['text']);
							db::query("UPDATE game_alliance SET `ally_description`='" . $ally['ally_description'] . "' WHERE `id`='" . $ally['id'] . "'");
						}
					}
	
					if ($t == 3)
					{
						$parse['text'] = $ally['ally_request'];
						$parse['Show_of_request_text'] = "Текст заявок альянса";
					}
					elseif ($t == 2)
					{
						$parse['text'] = $ally['ally_text'];
						$parse['Show_of_request_text'] = "Внутренний текст альянса";
					}
					else
						$parse['text'] = $ally['ally_description'];
	
					$parse['t'] = $t;
					$parse['ally_owner'] = $ally['ally_owner'];
					$parse['ally_web'] = $ally['ally_web'];
					$parse['ally_image'] = $ally['ally_image'];
					$parse['ally_request_notallow_0'] = ($ally['ally_request_notallow'] == 1) ? ' SELECTED' : '';
					$parse['ally_request_notallow_1'] = ($ally['ally_request_notallow'] == 0) ? ' SELECTED' : '';
					$parse['ally_owner_range'] = $ally['ally_owner_range'];
					$parse['Transfer_alliance'] = $this->MessageForm("Покинуть / Передать альянс", "", "?set=alliance&mode=admin&edit=give", 'Продолжить');
					$parse['Disolve_alliance'] = $this->MessageForm("Расформировать альянс", "", "?set=alliance&mode=admin&edit=exit", 'Продолжить');
	
					$this->setTemplate('alliance/alliance_admin');
					$this->set('parse', $parse);

					$this->setTitle(_getText('Alliance_admin'));
					$this->showTopPanel(false);
					$this->display();
				}
				elseif ($edit == 'requests')
				{
					if ($ally['ally_owner'] != user::get()->data['id'] && !$user_bewerbungen_bearbeiten)
						$this->message(_getText('Denied_access'), _getText('Check_the_requests'));
	
					if (isset($_POST['action']) && $_POST['action'] == "Принять")
					{
						if ($ally['ally_members'] >= 150)
							$this->message('Альянс не может иметь больше 150 участников', _getText('Check_the_requests'));
						else
						{
							if ($_POST['text'] != '')
								$text_ot = db::escape_string(strip_tags($_POST['text']));
	
							$check_req = db::query("SELECT a_id FROM game_alliance_requests WHERE a_id = " . $ally['id'] . " AND u_id = " . intval($show) . ";", true);
	
							if (isset($check_req['a_id']))
							{
								db::query("DELETE FROM game_alliance_requests WHERE u_id = " . intval($show) . ";");
	
								db::query("INSERT INTO game_alliance_members (a_id, u_id, time) VALUES (" . $ally['id'] . ", " . intval($show) . ", " . time() . ")");
								db::query("UPDATE game_alliance SET ally_members = ally_members + 1 WHERE id='" . $ally['id'] . "'");
								db::query("UPDATE game_users SET ally_name = '" . $ally['ally_name'] . "', ally_id = '" . $ally['id'] . "', new_message = new_message + 1 WHERE id = '" . intval($show) . "'");
								db::query("INSERT INTO game_messages SET `message_owner`='" . intval($show) . "', `message_sender`='" . user::get()->data['id'] . "' , `message_time`='" . time() . "', `message_type`='2', `message_from`='{$ally['ally_tag']}', `message_text`='Привет!<br>Альянс <b>" . $ally['ally_name'] . "</b> принял вас в свои ряды!" . ((isset($text_ot)) ? "<br>Приветствие:<br>" . $text_ot . "" : "") . "'");
							}
						}
					}
					elseif (isset($_POST['action']) && $_POST['action'] == "Отклонить")
					{
						if ($_POST['text'] != '')
							$text_ot = db::escape_string(strip_tags($_POST['text']));
	
						db::query("DELETE FROM game_alliance_requests WHERE u_id = " . intval($show) . " AND a_id = " . $ally['id'] . ";");
						db::query("INSERT INTO game_messages SET `message_owner`='" . intval($show) . "', `message_sender`='" . user::get()->data['id'] . "' , `message_time`='" . time() . "', `message_type`='2', `message_from`='{$ally['ally_tag']}', `message_text`='Привет!<br>Альянс <b>" . $ally['ally_name'] . "</b> отклонил вашу кандидатуру!" . ((isset($text_ot)) ? "<br>Причина:<br>" . $text_ot . "" : "") . "'");
					}
	
					$parse = array();
					$parse['list'] = array();
	
					$query = db::query("SELECT u.id, u.username, r.* FROM game_alliance_requests r LEFT JOIN game_users u ON u.id = r.u_id WHERE a_id = '" . $ally['id'] . "'");
	
					while ($r = db::fetch_assoc($query))
					{
						if (isset($show) && $r['id'] == $show)
						{
							$s = array();
							$s['username'] = $r['username'];
							$s['ally_request_text'] = nl2br($r['request']);
							$s['id'] = $r['id'];
						}
	
						$r['time'] = datezone("Y-m-d H:i:s", $r['time']);
	
						$parse['list'][] = $r;
					}
	
					if (isset($show) && $show != 0 && count($parse['list']) > 0 && isset($s))
						$parse['request'] = $s;
					else
						$parse['request'] = '';
	
					$parse['ally_tag'] = $ally['ally_tag'];
	
					$this->setTemplate('alliance/alliance_requests');
					$this->set('parse', $parse);

					$this->setTitle(_getText('Check_the_requests'));
					$this->showTopPanel(false);
					$this->display();
				}
				elseif ($edit == 'name')
				{
					if ($ally['ally_owner'] != user::get()->data['id'] && !$user_admin)
						$this->message(_getText('Denied_access'), _getText('Members_list'));
	
					if (isset($_POST['newname']))
					{
						if (!preg_match("/^[a-zA-Zа-яА-Я0-9_\.\,\-\!\?\*\ ]+$/u", $_POST['newname']))
							$this->message("Название альянса содержит запрещённые символы", _getText('make_alliance'));
	
						$ally['ally_name'] = addslashes(htmlspecialchars($_POST['newname']));
						db::query("UPDATE game_alliance SET `ally_name` = '" . $ally['ally_name'] . "' WHERE `id` = '" . user::get()->data['ally_id'] . "';");
						db::query("UPDATE game_users SET `ally_name` = '" . $ally['ally_name'] . "' WHERE `ally_id` = '" . $ally['id'] . "';");
					}
	
					$parse['question'] = 'Введите новое название альянса';
					$parse['name'] = 'newname';
					$parse['form'] = 'name';
	
					$this->setTemplate('alliance/alliance_rename');
					$this->set('parse', $parse);

					$this->setTitle('Управление альянсом');
					$this->showTopPanel(false);
					$this->display();
				}
				elseif ($edit == 'tag')
				{
					if ($ally['ally_owner'] != user::get()->data['id'] && !$user_admin)
						$this->message(_getText('Denied_access'), _getText('Members_list'));
	
					if (isset($_POST['newtag']))
					{
						if (!preg_match('/^[a-zA-Zа-яА-Я0-9_\.\,\-\!\?\*\ ]+$/u', $_POST['newtag']))
							$this->message("Абревиатура альянса содержит запрещённые символы", _getText('make_alliance'));
	
						$ally['ally_tag'] = addslashes(htmlspecialchars($_POST['newtag']));
						db::query("UPDATE game_alliance SET `ally_tag` = '" . $ally['ally_tag'] . "' WHERE `id` = '" . user::get()->data['ally_id'] . "';");
					}
	
					$parse['question'] = 'Введите новую аббревиатуру альянса';
					$parse['name'] = 'newtag';
					$parse['form'] = 'tag';
	
					$this->setTemplate('alliance/alliance_rename');
					$this->set('parse', $parse);

					$this->setTitle('Управление альянсом');
					$this->showTopPanel(false);
					$this->display();
				}
				elseif ($edit == 'exit')
				{
					if ($ally['ally_owner'] != user::get()->data['id'] && !$user_can_exit_alliance)
						$this->message(_getText('Denied_access'), _getText('Members_list'));
	
					db::query("UPDATE game_planets SET id_ally = 0 WHERE id_ally = ".$ally['id']."");
	
					db::query("UPDATE game_users SET `ally_id` = '0', `ally_name` = '' WHERE ally_id = '" . $ally['id'] . "'");
					db::query("DELETE FROM game_alliance WHERE id = '" . $ally['id'] . "'");
					db::query("DELETE FROM game_alliance_members WHERE a_id = '" . $ally['id'] . "'");
					db::query("DELETE FROM game_alliance_requests WHERE a_id = '" . $ally['id'] . "'");
					db::query("DELETE FROM game_alliance_diplomacy WHERE a_id = '" . $ally['id'] . "' OR d_id = '" . $ally['id'] . "'");
	
					request::redirectTo('?set=alliance');
				}
				elseif ($edit == 'give')
				{
					if ($ally['ally_owner'] != user::get()->data['id'])
						$this->message("Доступ запрещён.", "Ошибка!", "?set=alliance", 2);
	
					if (isset($_POST['newleader']) && $ally['ally_owner'] == user::get()->data['id'])
					{
						$info = db::query("SELECT id, ally_id FROM game_users WHERE id = '" . intval($_POST['newleader']) . "'", true);
	
						if (!$info['id'] || $info['ally_id'] != user::get()->data['ally_id'])
							$this->message("Операция невозможна.", "Ошибка!", "?set=alliance", 2);
	
						db::query("UPDATE game_alliance SET `ally_owner` = '" . $info['id'] . "' WHERE `id` = " . user::get()->data['ally_id'] . " ");
						db::query("UPDATE game_alliance_members SET `rank` = '0' WHERE `u_id` = '" . $info['id'] . "';");
	
						request::redirectTo('?set=alliance');
					}
	
					$listuser = db::query("SELECT u.username, u.id, m.rank FROM game_users u LEFT JOIN game_alliance_members m ON m.u_id = u.id WHERE u.ally_id = '" . user::get()->data['ally_id'] . "' AND u.id != " . $ally['ally_owner'] . " AND m.rank != 0;");
	
					$parse['righthand'] = '';
	
					while ($u = db::fetch_assoc($listuser))
					{
						if ($ally_ranks[$u['rank'] - 1]['rechtehand'] == 1)
							$parse['righthand'] .= "<option value=\"" . $u['id'] . "\">" . $u['username'] . "&nbsp;[" . $ally_ranks[$u['rank'] - 1]['name'] . "]&nbsp;&nbsp;</option>";
					}
	
					$parse['id'] = user::get()->data['id'];
	
					$this->setTemplate('alliance/alliance_transfer');
					$this->set('parse', $parse);

					$this->setTitle('Передача альянса');
					$this->showTopPanel(false);
					$this->display();
				}
			}
	
			if ($ally['ally_owner'] == user::get()->data['id'])
				$range = ($ally['ally_owner_range'] == '') ? 'Основатель' : $ally['ally_owner_range'];
			elseif ($ally_member['rank'] != 0 && isset($ally_ranks[$ally_member['rank'] - 1]['name']))
				$range = $ally_ranks[$ally_member['rank'] - 1]['name'];
			else
				$range = _getText('member');
	
			if ($user_diplomacy)
			{
				$qq = db::query("SELECT count(id) AS cc FROM game_alliance_diplomacy WHERE d_id = " . $ally['id'] . " AND status = 0", true);
	
				if ($qq['cc'] > 0)
					$parse['ally_dipl'] = " <a href=\"?set=alliance&mode=diplo\">Просмотр</a> (" . $qq['cc'] . " новых запросов)";
				else
					$parse['ally_dipl'] = " <a href=\"?set=alliance&mode=diplo\">Просмотр</a>";
			}
	
			$parse['requests'] = '';
	
			$request = db::first(db::query("SELECT COUNT(*) AS num FROM game_alliance_requests WHERE a_id = '" . $ally['id'] . "'", true));
	
			if ($request != 0)
			{
				if ($ally['ally_owner'] == user::get()->data['id'] || $ally_ranks[$ally_member['rank'] - 1]['bewerbungen'] != 0)
					$parse['requests'] = "<tr><th>Заявки</th><th><a href=\"?set=alliance&mode=admin&edit=requests\">" . $request . " заявок</a></th></tr>";
			}
	
			$parse['alliance_admin'] = ($user_admin) ? '(<a href="?set=alliance&mode=admin&edit=ally">управление альянсом</a>)' : '';
			$parse['send_circular_mail'] = ($user_can_send_mails) ? '<tr><th>Альянс чат (' . user::get()->data['mnl_alliance'] . ' новых)</th><th><a href="?set=alliance&mode=circular">Войти в чат</a></th></tr>' : '';
			$parse['members_list'] = ($user_can_watch_memberlist) ? ' (<a href="?set=alliance&mode=memberslist">список</a>)' : '';
			$parse['ally_owner'] = ($ally['ally_owner'] != user::get()->data['id']) ? $this->MessageForm(_getText('Exit_of_this_alliance'), "", "?set=alliance&mode=exit", _getText('Continue')) : '';
			$parse['ally_image'] = ($ally['ally_image'] != '') ? "<tr><th colspan=2 class=nopadding><img src=\"" . $ally['ally_image'] . "\" style=\"max-width:100%\"></th></tr>" : '';
			$parse['range'] = $range;
			$parse['ally_description'] = $ally['ally_description'];
			$parse['ally_text'] = $ally['ally_text'];
			$parse['ally_web'] = $ally['ally_web'];
			$parse['ally_tag'] = $ally['ally_tag'];
			$parse['ally_members'] = $ally['ally_members'];
			$parse['ally_name'] = $ally['ally_name'];
	
			$this->setTemplate('alliance/alliance_frontpage');
			$this->set('parse', $parse);

			$this->setTitle('Ваш альянс');
			$this->showTopPanel(false);
			$this->display();
		}
	}

	public function ainfo ()
	{
		$a = @intval($_GET['a']);
		$tag = @db::escape_string($_GET['tag']);

		if ($tag != "")
			$allyrow = db::query("SELECT * FROM game_alliance WHERE ally_tag = '" . $tag . "'", true);
		elseif ($a != 0)
			$allyrow = db::query("SELECT * FROM game_alliance WHERE id = '" . $a . "'", true);
		else
			$this->message("Указанного альянса не существует в игре!", "Информация об альянсе");

		if (!isset($allyrow['id']))
			$this->message("Указанного альянса не существует в игре!", "Информация об альянсе");

		if ($allyrow['ally_image'] != "")
			$allyrow['ally_image'] = "<tr><th colspan=2><img src=\"" . $allyrow['ally_image'] . "\" style=\"max-width:100%\"></th></tr>";

		if ($allyrow['ally_description'] == "")
			$allyrow['ally_description'] = "[center]У этого альянса ещё нет описания[/center]";

		if ($allyrow['ally_web'] != "")
			$allyrow['ally_web'] = "<tr><th>Сайт альянса:</th><th><a href=\"" . $allyrow['ally_web'] . "\" target=\"_blank\">" . $allyrow['ally_web'] . "</a></th></tr>";

		$parse['ally_member_scount'] = $allyrow['ally_members'];
		$parse['ally_name'] = $allyrow['ally_name'];
		$parse['ally_tag'] = $allyrow['ally_tag'];
		$parse['ally_description'] = $allyrow['ally_description'];
		$parse['ally_image'] = $allyrow['ally_image'];
		$parse['ally_web'] = $allyrow['ally_web'];
		$parse['bewerbung'] = (user::get()->data['ally_id'] == 0) ? "<tr><th>Вступление</th><th><a href=\"?set=alliance&mode=apply&amp;allyid=" . $allyrow['id'] . "\">Нажмите сюда для подачи заявки</a></th></tr>" : '';

		$this->setTemplate('alliance/alliance_info');
		$this->set('parse', $parse);

		$this->setTitle('Альянс ' . $allyrow['ally_name']);
		$this->showTopPanel(false);
		$this->display();
	}

	public function make ()
	{
		if (!user::get()->isAuthorized())
			$this->message(_getText('Denied_access'), "Ошипко");
			
		$ally_request = db::first(db::query("SELECT COUNT(*) AS num FROM game_alliance_requests WHERE u_id = " . user::get()->data['id'] . ";", true));

		if (user::get()->data['ally_id'] > 0 || $ally_request > 0)
			$this->show();

		if (isset($_GET['yes']) && $_POST)
		{
			if (!$_POST['atag'])
				$this->message(_getText('have_not_tag'), _getText('make_alliance'));
			if (!$_POST['aname'])
				$this->message(_getText('have_not_name'), _getText('make_alliance'));
			if (!preg_match('/^[a-zA-Zа-яА-Я0-9_\.\,\-\!\?\*\ ]+$/u', $_POST['atag']))
				$this->message("Абревиатура альянса содержит запрещённые символы", _getText('make_alliance'));
			if (!preg_match('/^[a-zA-Zа-яА-Я0-9_\.\,\-\!\?\*\ ]+$/u', $_POST['aname']))
				$this->message("Название альянса содержит запрещённые символы", _getText('make_alliance'));

			$tagquery = db::query("SELECT * FROM game_alliance WHERE ally_tag = '" . addslashes($_POST['atag']) . "'", true);

			if ($tagquery)
				$this->message(str_replace('%s', $_POST['atag'], _getText('always_exist')), _getText('make_alliance'));

			db::query("INSERT INTO game_alliance SET `ally_name` = '" . addslashes($_POST['aname']) . "', `ally_tag`= '" . addslashes($_POST['atag']) . "' , `ally_owner` = '" . user::get()->data['id'] . "', `ally_register_time` = " . time());

			$ally_id = db::insert_id();

			db::query("UPDATE game_users SET `ally_id` = '" . $ally_id . "', `ally_name` = '" . addslashes($_POST['aname']) . "' WHERE `id` = '" . user::get()->data['id'] . "'");
			db::query("INSERT INTO game_alliance_members (a_id, u_id, time) VALUES (" . $ally_id . ", " . user::get()->data['id'] . ", " . time() . ")");

			$this->setTitle(_getText('make_alliance'));
			$this->setContent($this->MessageForm(str_replace('%s', $_POST['atag'], _getText('ally_maked')), str_replace('%s', $_POST['atag'], _getText('alliance_has_been_maked')) . "<br><br>", "?set=alliance", _getText('Ok')));
			$this->showTopPanel(false);
			$this->display();
		}
		else
		{
			$this->setTemplate('alliance/alliance_make');

			$this->setTitle(_getText('make_alliance'));
			$this->showTopPanel(false);
			$this->display();
		}
	}

	public function search ()
	{
		if (!user::get()->isAuthorized())
			$this->message(_getText('Denied_access'), "Ошипко");
	
		$ally_request = db::first(db::query("SELECT COUNT(*) AS num FROM game_alliance_requests WHERE u_id = " . user::get()->data['id'] . ";", true));

		if (user::get()->data['ally_id'] > 0 || $ally_request > 0)
			$this->show();

		$parse = array();

		if (isset($_POST['searchtext']) && $_POST['searchtext'] != '')
		{
			if (!preg_match('/^[a-zA-Zа-яА-Я0-9_\.\,\-\!\?\*\ ]+$/u', $_POST['searchtext']))
				$this->message("Строка поиска содержит запрещённые символы", _getText('make_alliance'), '?set=alliance&mode=search', 2);

			$search = db::query("SELECT * FROM game_alliance WHERE ally_name LIKE '%" . $_POST['searchtext'] . "%' or ally_tag LIKE '%" . $_POST['searchtext'] . "%' LIMIT 30");

			$parse['result'] = array();

			if (db::num_rows($search) != 0)
			{
				while ($s = db::fetch_assoc($search))
				{
					$entry = array();
					$entry['ally_tag'] = "[<a href=\"?set=alliance&mode=apply&allyid={$s['id']}\">{$s['ally_tag']}</a>]";
					$entry['ally_name'] = $s['ally_name'];
					$entry['ally_members'] = $s['ally_members'];

					$parse['result'][] = $entry;
				}
			}
		}

		$parse['searchtext'] = (isset($_POST['searchtext'])) ? $_POST['searchtext'] : '';

		$this->setTemplate('alliance/alliance_search');
		$this->set('parse', $parse);

		$this->setTitle(_getText('search_alliance'));
		$this->showTopPanel(false);
		$this->display();
	}

	public function apply ()
	{
		if (!user::get()->isAuthorized())
			$this->message(_getText('Denied_access'), "Ошипко");
	
		if (user::get()->data['ally_id'] > 0)
			$this->show();

		if (!is_numeric($_GET['allyid']) || !$_GET['allyid'])
			$this->message(_getText('it_is_not_posible_to_apply'), _getText('it_is_not_posible_to_apply'));

		$allyid = intval($_GET['allyid']);

		$allyrow = db::query("SELECT ally_tag, ally_request, ally_request_notallow FROM game_alliance WHERE id = '" . $allyid . "'", true);

		if (!isset($allyrow['ally_tag']))
			$this->message("Альянса не существует!", "Ошибка");

		if ($allyrow['ally_request_notallow'] != 0)
			$this->message("Данный альянс является закрытым для вступлений новых членов", "Ошибка");

		if (isset($_POST['further']))
		{
			$request = db::query("SELECT COUNT(*) AS num FROM game_alliance_requests WHERE a_id = " . $allyid . " AND u_id = " . user::get()->data['id'] . ";", true);

			if ($request['num'] == 0)
			{
				db::query("INSERT INTO game_alliance_requests VALUES (" . $allyid . ", " . user::get()->data['id'] . ", " . time() . ", '" . db::escape_string(strip_tags($_POST['text'])) . "')");
				$this->message(_getText('apply_registered'), _getText('your_apply'), '?set=alliance', 3);
			}
			else
				$this->message('Вы уже отсылали заявку на вступление в этот альянс!', 'Ошибка', '?set=alliance', 3);
		}

		$parse = array();

		$parse['allyid'] = $allyid;
		$parse['text_apply'] = ($allyrow['ally_request']) ? $allyrow['ally_request'] : '';
		$parse['ally_tag'] = $allyrow['ally_tag'];

		$this->setTemplate('alliance/alliance_applyform');
		$this->set('parse', $parse);

		$this->setTitle('Запрос на вступление');
		$this->showTopPanel(false);
		$this->display();
	}

	public function stat ()
	{
		global $session;

		if (!$session->isAuthorized())
			$this->show();

		$allyid = request::R('id', 0);

		$allyrow = db::query("SELECT id, ally_name FROM game_alliance WHERE id = '" . $allyid . "'", true);

		if (!isset($allyrow['id']))
			$this->message('Информация о данном альянсе не найдена');

		$parse = array();
		$parse['name'] = $allyrow['ally_name'];
		$parse['data'] = db::extractResult(db::query("SELECT * FROM game_log_stats WHERE id = ".$allyid." AND type = 2 ORDER BY time ASC"));

		$this->setTemplate('alliance/alliance_stat');
		$this->set('parse', $parse);

		$this->setTitle('Статистика альянса');
		$this->showTopPanel(false);
		$this->display();
	}

	private function MessageForm ($Title, $Message, $Goto = '', $Button = ' ok ', $TwoLines = false)
	{
		$Form = "<form action=\"" . $Goto . "\" method=\"post\">";
		$Form .= "<table width=\"100%\"><tr>";
		$Form .= "<td class=\"c\">" . $Title . "</td>";
		$Form .= "</tr><tr>";

		if ($TwoLines == true)
		{
			$Form .= "<th >" . $Message . "</th>";
			$Form .= "</tr><tr>";
			$Form .= "<th align=\"center\"><input type=\"submit\" value=\"" . $Button . "\"></th>";
		}
		else
			$Form .= "<th>" . $Message . "<input type=\"submit\" value=\"" . $Button . "\"></th>";

		$Form .= "</tr></table></form>";

		return $Form;
	}
}

?>