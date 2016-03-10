<?php

namespace App\Controllers;

use App\Helpers;
use App\Lang;
use App\Models\Alliance;
use App\Models\AllianceMember;
use App\Sql;

class AllianceController extends ApplicationController
{
	/**
	 * @var \App\Models\Alliance $ally
	 */
	private $ally;

	public function initialize ()
	{
		parent::initialize();

		Lang::includeLang('alliance');
	}

	private function parseInfo ($allyId)
	{
		$ally = Alliance::findFirst($allyId);

		if (!$ally)
		{
			$this->db->updateAsDict('game_users', ['ally_id' => 0], 'id = '.$this->user->id);
			$this->db->delete('game_alliance_members', 'u_id = ?', [$this->user->id]);

			$this->message(_getText('ally_notexist'), _getText('your_alliance'), '/alliance/');
			die();
		}

		$this->ally = $ally;
		$this->ally->getMember($this->user->id);
		$this->ally->getRanks();

		if (!$this->ally->member)
		{
			$this->db->query('game_alliance_members', 'u_id = ?', [$this->user->id]);

			$this->ally->member = new AllianceMember();
			$this->ally->member->a_id = $this->ally->id;
			$this->ally->member->u_id = $this->user->id;
			$this->ally->member->time = time();

			$this->ally->member->save();
		}
	}

	private function noAlly ()
	{
		if (isset($_POST['bcancel']) && isset($_POST['r_id']))
		{
			$this->db->query("DELETE FROM game_alliance_requests WHERE a_id = " . intval($_POST['r_id']) . " AND `u_id` = " . $this->user->id);

			$this->message("Вы отозвали свою заявку на вступление в альянс", "Отзыв заявки", "/alliance/", 2);
		}

		$parse = [];

		$parse['list'] = [];

		$requests = $this->db->query("SELECT r.*, a.name, a.tag FROM game_alliance_requests r LEFT JOIN game_alliance a ON a.id = r.a_id WHERE r.u_id = " . $this->user->id . ";");

		while ($request = $requests->fetch())
			$parse['list'][] = array($request['a_id'], $request['tag'], $request['name'], $request['time']);

		$parse['allys'] = [];

		$allys = $this->db->query("SELECT s.total_points, a.`id`, a.`tag`, a.`name`, a.`members` FROM game_statpoints s, game_alliance a WHERE s.`stat_type` = '2' AND s.`stat_code` = '1' AND a.id = s.id_owner ORDER BY s.`total_points` DESC LIMIT 0,15;");

		while ($ally = $allys->fetch())
		{
			$ally['total_points'] = Helpers::pretty_number($ally['total_points']);
			$parse['allys'][] = $ally;
		}

		$this->view->pick('alliance/default');
		$this->view->setVar('parse', $parse);

		$this->tag->setTitle(_getText('alliance'));
		$this->showTopPanel(false);
	}
	
	public function indexAction ()
	{
		if (!$this->auth->isAuthorized())
			$this->message(_getText('Denied_access'), "Ошипко");
	
		if ($this->user->ally_id == 0)
			$this->noAlly();
		else
		{
			$this->parseInfo($this->user->ally_id);
	
			if ($this->ally->owner == $this->user->id)
				$range = ($this->ally->owner_range == '') ? 'Основатель' : $this->ally->owner_range;
			elseif ($this->ally->member->rank != 0 && isset($this->ally->ranks[$this->ally->member->rank - 1]['name']))
				$range = $this->ally->ranks[$this->ally->member->rank - 1]['name'];
			else
				$range = _getText('member');

			$parse['diplomacy'] = false;
	
			if ($this->ally->canAccess(Alliance::DIPLOMACY_ACCESS))
				$parse['diplomacy'] = $this->db->fetchColumn("SELECT count(id) FROM game_alliance_diplomacy WHERE d_id = " . $this->ally->id . " AND status = 0");

			$parse['requests'] = 0;

			if ($this->ally->owner == $this->user->id || $this->ally->canAccess(Alliance::REQUEST_ACCESS))
				$parse['requests'] = $this->db->fetchColumn("SELECT COUNT(*) AS num FROM game_alliance_requests WHERE a_id = '" . $this->ally->id . "'");

			$parse['alliance_admin'] = $this->ally->canAccess(Alliance::ADMIN_ACCESS);
			$parse['chat_access'] = $this->ally->canAccess(Alliance::CHAT_ACCESS);
			$parse['members_list'] = $this->ally->canAccess(Alliance::CAN_WATCH_MEMBERLIST);
			$parse['owner'] = ($this->ally->owner != $this->user->id) ? $this->MessageForm(_getText('Exit_of_this_alliance'), "", "/alliance/exit/", _getText('Continue')) : '';
			$parse['image'] = $this->ally->image;
			$parse['range'] = $range;
			$parse['description'] = $this->ally->description;
			$parse['text'] = $this->ally->text;
			$parse['web'] = $this->ally->web;
			$parse['tag'] = $this->ally->tag;
			$parse['members'] = $this->ally->members;
			$parse['name'] = $this->ally->name;

			$this->view->setVar('parse', $parse);

			$this->tag->setTitle('Ваш альянс');
			$this->showTopPanel(false);
		}

		return true;
	}

	public function adminAction ()
	{
		$edit = $this->request->getQuery('edit', null, '');

		$this->parseInfo($this->user->ally_id);

		if ($this->user->isAdmin() && $edit == 'planets')
		{
			if ($this->ally->owner != $this->user->id)
				$this->message(_getText('Denied_access'), "Управление планетами");

			$parse = [];

			$parse['list'] = $this->db->extractResult($this->db->query("SELECT id, id_ally, name, galaxy, system, planet FROM game_planets WHERE planet_type = 5 AND id_owner = ".$this->user->id.""));
			$parse['credits'] = $this->user->credits;

			$parse['bases'] = $this->db->fetchColumn("SELECT COUNT(*) AS num FROM game_planets WHERE planet_type = 5 AND id_ally = ".$this->ally->id."");

			$parse['need'] = 100 * ($parse['bases'] > 0 ? (5 + ((int) $parse['bases'] - 1) * 5) : 1);

			if (isset($_GET['ally']))
			{
				$id = intval($_GET['ally']);

				$check = $this->db->query("SELECT id, id_ally FROM game_planets WHERE planet_type = 5 AND id_ally = 0 AND id_owner = ".$this->user->id." AND id = ".$id."")->fetch();

				if (isset($check['id']))
				{
					if ($this->user->credits >= $parse['need'])
					{
						$this->db->updateAsDict('game_planets', ['id_ally' => $this->ally->id, 'name' => $this->ally->name], 'id = '.$check['id']);

						Sql::build()->update('game_users')->setField('-credits', $parse['need'])->where('id', '=', $this->user->id)->execute();

						$this->message("Планета была успешно преобразована", "Управление планетами", "/alliance/admin/edit/planets/", 3);
					}
					else
						$this->message("Недостаточно кредитов для преобразования планеты", "Управление планетами");
				}
			}

			$this->view->pick('alliance/planets');
			$this->view->setVar('parse', $parse);

			$this->tag->setTitle('Управление планетами');
			$this->showTopPanel(false);
		}

		if ($edit == 'rights')
		{
			if ($this->ally->owner != $this->user->id && !$this->ally->canAccess(Alliance::CAN_EDIT_RIGHTS) && $this->user->id != 1)
				$this->message(_getText('Denied_access'), _getText('Members_list'));
			elseif (!empty($_POST['newrangname']))
			{
				$this->ally->ranks[] = [
					'name' => strip_tags($this->request->getPost('newrangname')),
					Alliance::CAN_DELETE_ALLIANCE => 0,
					Alliance::CAN_KICK => 0,
					Alliance::REQUEST_ACCESS => 0,
					Alliance::CAN_WATCH_MEMBERLIST => 0,
					Alliance::CAN_ACCEPT => 0,
					Alliance::ADMIN_ACCESS => 0,
					Alliance::CAN_WATCH_MEMBERLIST_STATUS => 0,
					Alliance::CHAT_ACCESS => 0,
					Alliance::CAN_EDIT_RIGHTS => 0,
					Alliance::DIPLOMACY_ACCESS => 0,
					Alliance::PLANET_ACCESS => 0
				];

				$this->db->query("UPDATE game_alliance SET `ranks` = '" . addslashes(json_encode($this->ally->ranks)) . "' WHERE `id` = " . $this->ally->id);
			}
			elseif (isset($_POST['id']) && is_array($_POST['id']))
			{
				$ally_ranks_new = [];

				foreach ($_POST['id'] as $id)
				{
					$name = $this->ally->ranks[$id]['name'];

					$ally_ranks_new[$id]['name'] = $name;

					$ally_ranks_new[$id][Alliance::CAN_DELETE_ALLIANCE] = ($this->ally->owner == $this->user->id ? (isset($_POST['u' . $id . 'r0']) ? 1 : 0) : $this->ally->ranks[$id][Alliance::CAN_DELETE_ALLIANCE]);
					$ally_ranks_new[$id][Alliance::CAN_KICK] = ($this->ally->owner == $this->user->id ? (isset($_POST['u' . $id . 'r1']) ? 1 : 0) : $this->ally->ranks[$id][Alliance::CAN_KICK]);
					$ally_ranks_new[$id][Alliance::REQUEST_ACCESS] = (isset($_POST['u' . $id . 'r2'])) ? 1 : 0;
					$ally_ranks_new[$id][Alliance::CAN_WATCH_MEMBERLIST] = (isset($_POST['u' . $id . 'r3'])) ? 1 : 0;
					$ally_ranks_new[$id][Alliance::CAN_ACCEPT] = (isset($_POST['u' . $id . 'r4'])) ? 1 : 0;
					$ally_ranks_new[$id][Alliance::ADMIN_ACCESS] = (isset($_POST['u' . $id . 'r5'])) ? 1 : 0;
					$ally_ranks_new[$id][Alliance::CAN_WATCH_MEMBERLIST_STATUS] = (isset($_POST['u' . $id . 'r6'])) ? 1 : 0;
					$ally_ranks_new[$id][Alliance::CHAT_ACCESS] = (isset($_POST['u' . $id . 'r7'])) ? 1 : 0;
					$ally_ranks_new[$id][Alliance::CAN_EDIT_RIGHTS] = (isset($_POST['u' . $id . 'r8'])) ? 1 : 0;
					$ally_ranks_new[$id][Alliance::DIPLOMACY_ACCESS] = (isset($_POST['u' . $id . 'r9'])) ? 1 : 0;
					$ally_ranks_new[$id][Alliance::PLANET_ACCESS] = (isset($_POST['u' . $id . 'r10'])) ? 1 : 0;
				}

				$this->ally->ranks = $ally_ranks_new;

				$this->db->query("UPDATE game_alliance SET `ranks` = '" . addslashes(json_encode($this->ally->ranks)) . "' WHERE `id` = " . $this->ally->id);
			}
			elseif ($this->request->hasQuery('d') && isset($this->ally->ranks[$this->request->getQuery('d', 'int')]))
			{
				unset($this->ally->ranks[$this->request->getQuery('d', 'int')]);

				$this->db->query("UPDATE game_alliance SET `ranks` = '" . addslashes(json_encode($this->ally->ranks)) . "' WHERE `id` = " . $this->ally->id);
			}

			$parse['list'] = [];

			if (is_array($this->ally->ranks) && count($this->ally->ranks) > 0)
			{
				foreach ($this->ally->ranks as $a => $b)
				{
					$list['id'] = $a;
					$list['delete'] = "<a href=\"/alliance/admin/edit/rights/d/".$a."/\"><img src=\"/assets/images/abort.gif\" alt=\"Удалить ранг\" border=0></a>";
					$list['r0'] = $b['name'];
					$list['a'] = $a;

					if ($this->ally->owner == $this->user->id)
						$list['r1'] = "<input type=checkbox name=\"u".$a."r0\"" . (($b[Alliance::CAN_DELETE_ALLIANCE] == 1) ? ' checked="checked"' : '') . ">";
					else
						$list['r1'] = "<b>" . (($b['delete'] == 1) ? '+' : '-') . "</b>";

					if ($this->ally->owner == $this->user->id)
						$list['r2'] = "<input type=checkbox name=\"u".$a."r1\"" . (($b[Alliance::CAN_KICK] == 1) ? ' checked="checked"' : '') . ">";
					else
						$list['r2'] = "<b>" . (($b['kick'] == 1) ? '+' : '-') . "</b>";

					$list['r3']  = "<input type=checkbox name=\"u".$a."r2\"" .  (($b[Alliance::REQUEST_ACCESS] == 1) ? ' checked="checked"' : '') . ">";
					$list['r4']  = "<input type=checkbox name=\"u".$a."r3\"" .  (($b[Alliance::CAN_WATCH_MEMBERLIST] == 1) ? ' checked="checked"' : '') . ">";
					$list['r5']  = "<input type=checkbox name=\"u".$a."r4\"" .  (($b[Alliance::CAN_ACCEPT] == 1) ? ' checked="checked"' : '') . ">";
					$list['r6']  = "<input type=checkbox name=\"u".$a."r5\"" .  (($b[Alliance::ADMIN_ACCESS] == 1) ? ' checked="checked"' : '') . ">";
					$list['r7']  = "<input type=checkbox name=\"u".$a."r6\"" .  (($b[Alliance::CAN_WATCH_MEMBERLIST_STATUS] == 1) ? ' checked="checked"' : '') . ">";
					$list['r8']  = "<input type=checkbox name=\"u".$a."r7\"" .  (($b[Alliance::CHAT_ACCESS] == 1) ? ' checked="checked"' : '') . ">";
					$list['r9']  = "<input type=checkbox name=\"u".$a."r8\"" .  (($b[Alliance::CAN_EDIT_RIGHTS] == 1) ? ' checked="checked"' : '') . ">";
					$list['r10'] = "<input type=checkbox name=\"u".$a."r9\"" .  (($b[Alliance::DIPLOMACY_ACCESS] == 1) ? ' checked="checked"' : '') . ">";
					$list['r11'] = "<input type=checkbox name=\"u".$a."r10\"" . (($b[Alliance::PLANET_ACCESS] == 1) ? ' checked="checked"' : '') . ">";

					$parse['list'][] = $list;
				}
			}

			$this->view->pick('alliance/laws');
			$this->view->setVar('parse', $parse);

			$this->tag->setTitle(_getText('Law_settings'));
			$this->showTopPanel(false);
		}
		elseif ($edit == 'ally')
		{
			if ($this->ally->owner != $this->user->id && !$this->ally->canAccess(Alliance::ADMIN_ACCESS))
				$this->message(_getText('Denied_access'), "Меню управления альянсом");

			$t = $this->request->getQuery('t', 'int', 1);

			if ($t != 1 && $t != 2 && $t != 3)
				$t = 1;

			if (isset($_POST['options']))
			{
				$this->ally->owner_range = htmlspecialchars(strip_tags($_POST['owner_range']));
				$this->ally->web = htmlspecialchars(strip_tags($_POST['web']));
				$this->ally->image = htmlspecialchars(strip_tags($_POST['image']));
				$this->ally['request_notallow'] = intval($_POST['request_notallow']);

				if ($this->ally['request_notallow'] != 0 && $this->ally['request_notallow'] != 1)
					$this->message("Недопустимое значение атрибута!", "Ошибка");

				$this->db->query("UPDATE game_alliance SET `owner_range`='" . $this->ally->owner_range . "', `image`='" . $this->ally->image . "', `web`='" . $this->ally->web . "', `request_notallow`='" . $this->ally->request_notallow . "' WHERE `id`='" . $this->ally->id . "'");
			}
			elseif (isset($_POST['t']))
			{
				if ($t == 3)
				{
					$this->ally->request = Helpers::FormatText($_POST['text']);
					$this->db->query("UPDATE game_alliance SET `request`='" . $this->ally->request . "' WHERE `id`='" . $this->ally->id . "'");
				}
				elseif ($t == 2)
				{
					$this->ally->text = Helpers::FormatText($_POST['text']);
					$this->db->query("UPDATE game_alliance SET `text`='" . $this->ally->text . "' WHERE `id`='" . $this->ally->id . "'");
				}
				else
				{
					$this->ally->description = Helpers::FormatText($_POST['text']);
					$this->db->query("UPDATE game_alliance SET `description`='" . $this->ally->description . "' WHERE `id`='" . $this->ally->id . "'");
				}
			}

			if ($t == 3)
			{
				$parse['text'] = $this->ally->request;
				$parse['Show_of_request_text'] = "Текст заявок альянса";
			}
			elseif ($t == 2)
			{
				$parse['text'] = $this->ally->text;
				$parse['Show_of_request_text'] = "Внутренний текст альянса";
			}
			else
				$parse['text'] = $this->ally->description;

			$parse['t'] = $t;
			$parse['owner'] = $this->ally->owner;
			$parse['web'] = $this->ally->web;
			$parse['image'] = $this->ally->image;
			$parse['request_notallow_0'] = ($this->ally->request_notallow == 1) ? ' SELECTED' : '';
			$parse['request_notallow_1'] = ($this->ally->request_notallow == 0) ? ' SELECTED' : '';
			$parse['owner_range'] = $this->ally->owner_range;
			$parse['Transfer_alliance'] = $this->MessageForm("Покинуть / Передать альянс", "", "/alliance/admin/edit/give/", 'Продолжить');
			$parse['Disolve_alliance'] = $this->MessageForm("Расформировать альянс", "", "/alliance/admin/edit/exit/", 'Продолжить');

			$this->view->pick('alliance/admin');
			$this->view->setVar('parse', $parse);

			$this->tag->setTitle(_getText('Alliance_admin'));
			$this->showTopPanel(false);
		}
		elseif ($edit == 'requests')
		{
			if ($this->ally->owner != $this->user->id && !$this->ally->canAccess(Alliance::CAN_ACCEPT) && !$this->ally->canAccess(Alliance::REQUEST_ACCESS))
				$this->message(_getText('Denied_access'), _getText('Check_the_requests'));

			if ($this->ally->owner == $this->user->id || $this->ally->canAccess(Alliance::CAN_ACCEPT))
			{
				$show = $this->request->getQuery('show', 'int', 0);

				if (isset($_POST['action']) && $_POST['action'] == "Принять")
				{
					if ($this->ally->members >= 150)
						$this->message('Альянс не может иметь больше 150 участников', _getText('Check_the_requests'));
					else
					{
						if ($_POST['text'] != '')
							$text_ot = strip_tags($_POST['text']);

						$check = $this->db->query("SELECT a_id FROM game_alliance_requests WHERE a_id = " . $this->ally->id . " AND u_id = " . $show . "")->fetch();

						if (isset($check['a_id']))
						{
							$this->db->delete('game_alliance_requests', "u_id = ?", [$show]);
							$this->db->delete('game_alliance_members', "u_id = ?", [$show]);

							$this->db->insertAsDict('game_alliance_members', ['a_id' => $this->ally->id, 'u_id' => $show, 'time' => time()]);

							$this->db->execute("UPDATE game_alliance SET members = members + 1 WHERE id = ?", [$this->ally->id]);
							$this->db->query("UPDATE game_users SET ally_name = '" . $this->ally->name . "', ally_id = '" . $this->ally->id . "' WHERE id = '" . $show . "'");

							$this->game->sendMessage($show, $this->user->id, 0, 2, $this->ally->tag, "Привет!<br>Альянс <b>" . $this->ally->name . "</b> принял вас в свои ряды!" . ((isset($text_ot)) ? "<br>Приветствие:<br>" . $text_ot . "" : ""));

							return $this->response->redirect("alliance/members/");
						}
					}
				}
				elseif (isset($_POST['action']) && $_POST['action'] == "Отклонить")
				{
					if ($_POST['text'] != '')
						$text_ot = strip_tags($_POST['text']);

					$this->db->delete('game_alliance_requests', "u_id = ? AND a_id = ?", [$show, $this->ally->id]);

					$this->game->sendMessage($show, $this->user->id, 0, 2, $this->ally->tag, "Привет!<br>Альянс <b>" . $this->ally->name . "</b> отклонил вашу кандидатуру!" . ((isset($text_ot)) ? "<br>Причина:<br>" . $text_ot . "" : ""));
				}
			}

			$parse = [];
			$parse['list'] = [];

			$query = $this->db->query("SELECT u.id, u.username, r.* FROM game_alliance_requests r LEFT JOIN game_users u ON u.id = r.u_id WHERE a_id = '" . $this->ally->id . "'");

			while ($r = $query->fetch())
			{
				if (isset($show) && $r['id'] == $show)
				{
					$s = [];
					$s['username'] = $r['username'];
					$s['request_text'] = nl2br($r['request']);
					$s['id'] = $r['id'];
				}

				$r['time'] = $this->game->datezone("Y-m-d H:i:s", $r['time']);

				$parse['list'][] = $r;
			}

			if (isset($show) && $show != 0 && count($parse['list']) > 0 && isset($s))
				$parse['request'] = $s;
			else
				$parse['request'] = '';

			$parse['tag'] = $this->ally->tag;

			$this->view->pick('alliance/requests');
			$this->view->setVar('parse', $parse);

			$this->tag->setTitle(_getText('Check_the_requests'));
			$this->showTopPanel(false);
		}
		elseif ($edit == 'name')
		{
			if ($this->ally->owner != $this->user->id && !$this->ally->canAccess(Alliance::ADMIN_ACCESS))
				$this->message(_getText('Denied_access'), _getText('Members_list'));

			if (isset($_POST['newname']))
			{
				if (!preg_match("/^[a-zA-Zа-яА-Я0-9_\.\,\-\!\?\*\ ]+$/u", $_POST['newname']))
					$this->message("Название альянса содержит запрещённые символы", _getText('make_alliance'));

				$this->ally->name = addslashes(htmlspecialchars($_POST['newname']));
				$this->db->query("UPDATE game_alliance SET `name` = '" . $this->ally->name . "' WHERE `id` = '" . $this->user->ally_id . "';");
				$this->db->query("UPDATE game_users SET `ally_name` = '" . $this->ally->name . "' WHERE `ally_id` = '" . $this->ally->id . "';");
			}

			$parse['question'] = 'Введите новое название альянса';
			$parse['name'] = 'newname';
			$parse['form'] = 'name';

			$this->view->pick('alliance/rename');
			$this->view->setVar('parse', $parse);

			$this->tag->setTitle('Управление альянсом');
			$this->showTopPanel(false);
		}
		elseif ($edit == 'tag')
		{
			if ($this->ally->owner != $this->user->id && !$this->ally->canAccess(Alliance::ADMIN_ACCESS))
				$this->message(_getText('Denied_access'), _getText('Members_list'));

			if (isset($_POST['newtag']))
			{
				if (!preg_match('/^[a-zA-Zа-яА-Я0-9_\.\,\-\!\?\*\ ]+$/u', $_POST['newtag']))
					$this->message("Абревиатура альянса содержит запрещённые символы", _getText('make_alliance'));

				$this->ally->tag = addslashes(htmlspecialchars($_POST['newtag']));
				$this->db->query("UPDATE game_alliance SET `tag` = '" . $this->ally->tag . "' WHERE `id` = '" . $this->user->ally_id . "';");
			}

			$parse['question'] = 'Введите новую аббревиатуру альянса';
			$parse['name'] = 'newtag';
			$parse['form'] = 'tag';

			$this->view->pick('alliance/rename');
			$this->view->setVar('parse', $parse);

			$this->tag->setTitle('Управление альянсом');
			$this->showTopPanel(false);
		}
		elseif ($edit == 'exit')
		{
			if ($this->ally->owner != $this->user->id && !$this->ally->canAccess(Alliance::CAN_DELETE_ALLIANCE))
				$this->message(_getText('Denied_access'), _getText('Members_list'));

			$this->ally->deleteAlly();

			$this->response->redirect('alliance/');
		}
		elseif ($edit == 'give')
		{
			if ($this->ally->owner != $this->user->id)
				$this->message("Доступ запрещён.", "Ошибка!", "/alliance/", 2);

			if (isset($_POST['newleader']) && $this->ally->owner == $this->user->id)
			{
				$info = $this->db->query("SELECT id, ally_id FROM game_users WHERE id = '" . intval($_POST['newleader']) . "'")->fetch();

				if (!$info['id'] || $info['ally_id'] != $this->user->ally_id)
					$this->message("Операция невозможна.", "Ошибка!", "/alliance/", 2);

				$this->db->query("UPDATE game_alliance SET `owner` = '" . $info['id'] . "' WHERE `id` = " . $this->user->ally_id . " ");
				$this->db->query("UPDATE game_alliance_members SET `rank` = '0' WHERE `u_id` = '" . $info['id'] . "';");

				$this->response->redirect('alliance/');
			}

			$listuser = $this->db->query("SELECT u.username, u.id, m.rank FROM game_users u LEFT JOIN game_alliance_members m ON m.u_id = u.id WHERE u.ally_id = '" . $this->user->ally_id . "' AND u.id != " . $this->ally->owner . " AND m.rank != 0;");

			$parse['righthand'] = '';

			while ($u = $listuser->fetch())
			{
				if ($this->ally->ranks[$u['rank'] - 1]['rechtehand'] == 1)
					$parse['righthand'] .= "<option value=\"" . $u['id'] . "\">" . $u['username'] . "&nbsp;[" . $this->ally->ranks[$u['rank'] - 1]['name'] . "]&nbsp;&nbsp;</option>";
			}

			$parse['id'] = $this->user->id;

			$this->view->pick('alliance/transfer');
			$this->view->setVar('parse', $parse);

			$this->tag->setTitle('Передача альянса');
			$this->showTopPanel(false);
		}
		elseif ($edit == 'members')
		{
			if ($this->ally->owner != $this->user->id && !$this->ally->canAccess(Alliance::CAN_KICK))
				$this->message(_getText('Denied_access'), _getText('Members_list'));

			if ($this->request->hasQuery('kick'))
			{
				$kick = $this->request->getQuery('kick', 'int', 0);

				if ($this->ally->owner != $this->user->id && !$this->ally->canAccess(Alliance::CAN_KICK) && $kick > 0)
					$this->message(_getText('Denied_access'), _getText('Members_list'));

				$u = $this->db->query("SELECT * FROM game_users WHERE id = '" . $kick . "' LIMIT 1")->fetch();

				if ($u['ally_id'] == $this->ally->id && $u['id'] != $this->ally->owner)
				{
					$this->db->query("UPDATE game_planets SET id_ally = 0 WHERE id_owner = ".$u['id']." AND id_ally = ".$this->ally->id."");

					$this->db->query("UPDATE game_users SET `ally_id` = '0', `ally_name` = '' WHERE `id` = '" . $u['id'] . "'");
					$this->db->query("DELETE FROM game_alliance_members WHERE u_id = " . $u['id'] . ";");
				}
				else
					$this->message(_getText('Denied_access'), _getText('Members_list'));
			}
			elseif ($this->request->getPost('newrang', null, '') != '' && $this->request->get('id', 'int', 0) != 0)
			{
				$id = $this->request->get('id', 'int', 0);
				$rank = $this->request->getPost('newrang', 'int', 0);

				$q = $this->db->query("SELECT `id`, `ally_id` FROM game_users WHERE id = '" . $id . "' LIMIT 1")->fetch();

				if ((isset($this->ally->ranks[$rank - 1]) || $rank == 0) && $q['id'] != $this->ally->owner && $q['ally_id'] == $this->ally->id)
					$this->db->query("UPDATE game_alliance_members SET `rank` = '" . $rank . "' WHERE `u_id` = '" . $id . "';");
			}

			$this->membersAction();
		}
		else
			$this->response->redirect('alliance/');

		return true;
	}

	public function diplomacyAction ()
	{
		$this->parseInfo($this->user->ally_id);

		if ($this->ally->owner != $this->user->id && !$this->ally->canAccess(Alliance::DIPLOMACY_ACCESS))
			$this->message(_getText('Denied_access'), "Дипломатия");

		$parse['DText'] = $parse['DMyQuery'] = $parse['DQuery'] = [];

		if ($this->request->hasQuery('edit'))
		{
			if ($this->request->getQuery('edit', null, '') == "add")
			{
				$st = intval($_POST['status']);
				$al = $this->db->query("SELECT id, name FROM game_alliance WHERE id = '" . intval($_POST['ally']) . "'")->fetch();

				if (!$al['id'])
					$this->message("Ошибка ввода параметров", "Дипломатия", "/alliance/diplomacy/", 3);

				$ad = $this->db->query("SELECT id FROM game_alliance_diplomacy WHERE a_id = " . $this->ally->id . " AND d_id = " . $al['id'] . ";");

				if ($ad->numRows() > 0)
					$this->message("У вас уже есть соглашение с этим альянсом. Разорвите старое соглашения прежде чем создать новое.", "Дипломатия", "/alliance/diplomacy/", 3);

				if ($st < 0 || $st > 3)
					$st = 0;

				$this->db->query("INSERT INTO game_alliance_diplomacy VALUES (NULL, " . $this->ally->id . ", " . $al['id'] . ", " . $st . ", 0, 1)");
				$this->db->query("INSERT INTO game_alliance_diplomacy VALUES (NULL, " . $al['id'] . ", " . $this->ally->id . ", " . $st . ", 0, 0)");

				$this->message("Отношение между вашими альянсами успешно добавлено", "Дипломатия", "/alliance/diplomacy/", 3);
			}
			elseif ($this->request->getQuery('edit', null, '') == "del")
			{
				$al = $this->db->query("SELECT a_id, d_id FROM game_alliance_diplomacy WHERE id = '" . intval($_GET['id']) . "' AND a_id = " . $this->ally->id . ";")->fetch();

				if (!$al['a_id'])
					$this->message("Ошибка ввода параметров", "Дипломатия", "/alliance/diplomacy/", 3);

				$this->db->query("DELETE FROM game_alliance_diplomacy WHERE a_id = " . $al['a_id'] . " AND d_id = " . $al['d_id'] . ";");
				$this->db->query("DELETE FROM game_alliance_diplomacy WHERE a_id = " . $al['d_id'] . " AND d_id = " . $al['a_id'] . ";");

				$this->message("Отношение между вашими альянсами расторжено", "Дипломатия", "/alliance/diplomacy/", 3);
			}
			elseif ($this->request->getQuery('edit', null, '') == "suc")
			{
				$al = $this->db->query("SELECT a_id, d_id FROM game_alliance_diplomacy WHERE id = '" . intval($_GET['id']) . "' AND a_id = " . $this->ally->id . "")->fetch();

				if (!$al['a_id'])
					$this->message("Ошибка ввода параметров", "Дипломатия", "/alliance/diplomacy/", 3);

				$this->db->query("UPDATE game_alliance_diplomacy SET status = 1 WHERE a_id = " . $al['a_id'] . " AND d_id = " . $al['d_id'] . ";");
				$this->db->query("UPDATE game_alliance_diplomacy SET status = 1 WHERE a_id = " . $al['d_id'] . " AND d_id = " . $al['a_id'] . ";");

				$this->message("Отношение между вашими альянсами подтверждено", "Дипломатия", "/alliance/diplomacy/", 3);
			}
		}

		$dp = $this->db->query("SELECT ad.*, a.name FROM game_alliance_diplomacy ad, game_alliance a WHERE a.id = ad.d_id AND ad.a_id = '" . $this->ally->id . "';");

		while ($diplo = $dp->fetch())
		{
			if ($diplo['status'] == 0)
			{
				if ($diplo['primary'] == 1)
					$parse['DMyQuery'][] = $diplo;
				else
					$parse['DQuery'][] = $diplo;
			}
			else
				$parse['DText'][] = $diplo;
		}

		$parse['a_list'] = [];

		$ally_list = $this->db->query("SELECT id, name, tag FROM game_alliance WHERE id != " . $this->user->ally_id . " AND members > 0");

		while ($a_list = $ally_list->fetch())
			$parse['a_list'][] = $a_list;

		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Дипломатия');
		$this->showTopPanel(false);
	}

	public function exitAction ()
	{
		if ($this->ally->owner == $this->user->id)
			$this->message(_getText('Owner_cant_go_out'), _getText('Alliance'));

		if ($this->request->hasQuery('yes'))
		{
			$this->ally->deleteMember($this->user->id);

			$html = $this->MessageForm(_getText('Go_out_welldone'), "<br>", '/alliance/', _getText('Ok'));
		}
		else
			$html = $this->MessageForm(_getText('Want_go_out'), "<br>", "/alliance/exit/yes/1/", "Подтвердить");

		$this->tag->setTitle('Выход их альянса');
		$this->view->setVar('html', $html);
		$this->showTopPanel(false);
	}

	public function membersAction ()
	{
		$this->parseInfo($this->user->ally_id);

		$this->view->pick('alliance/members');

		$parse = [];

		if ($this->dispatcher->getActionName() == 'admin')
			$parse['admin'] = true;
		else
		{
			if ($this->ally->owner != $this->user->id && !$this->ally->canAccess(Alliance::CAN_WATCH_MEMBERLIST))
				$this->message(_getText('Denied_access'), _getText('Members_list'));

			$parse['admin'] = false;
		}

		$sort1 = $this->request->getQuery('sort1', 'int', 0);
		$sort2 = $this->request->getQuery('sort2', 'int', 0);

		$rank = $this->request->getQuery('rank', 'int', 0);

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
			elseif ($sort1 == 5 && $this->ally->canAccess(Alliance::CAN_WATCH_MEMBERLIST_STATUS))
				$sort = " ORDER BY u.`onlinetime`";
			else
				$sort = " ORDER BY u.`id`";

			if ($sort2 == 1)
				$sort .= " DESC;";
			elseif ($sort2 == 2)
				$sort .= " ASC;";
		}
		$listuser = $this->db->query("SELECT u.id, u.username, u.race, u.galaxy, u.system, u.planet, u.onlinetime, m.rank, m.time, s.total_points FROM game_users u LEFT JOIN game_alliance_members m ON m.u_id = u.id LEFT JOIN game_statpoints s ON s.id_owner = u.id AND stat_type = 1 WHERE u.ally_id = '" . $this->user->ally_id . "'" . $sort . "");

		$i = 0;
		$parse['memberslist'] = [];

		while ($u = $listuser->fetch())
		{
			$i++;
			$u['i'] = $i;

			if ($u["onlinetime"] + 60 * 10 >= time() && $this->ally->canAccess(Alliance::CAN_WATCH_MEMBERLIST_STATUS))
				$u["onlinetime"] = "lime>" . _getText('On') . "<";
			elseif ($u["onlinetime"] + 60 * 20 >= time() && $this->ally->canAccess(Alliance::CAN_WATCH_MEMBERLIST_STATUS))
				$u["onlinetime"] = "yellow>" . _getText('15_min') . "<";
			elseif ($this->ally->canAccess(Alliance::CAN_WATCH_MEMBERLIST_STATUS))
			{
				$hours = floor((time() - $u["onlinetime"]) / 3600);
				$u["onlinetime"] = "red>" . _getText('Off') . " " . floor($hours / 24) . " д. " . ($hours % 24) . " ч.<";
			}

			if ($this->ally->owner == $u['id'])
				$u["range"] = ($this->ally->owner_range == '') ? "Основатель" : $this->ally->owner_range;
			elseif (isset($this->ally->ranks[$u['rank'] - 1]['name']))
				$u["range"] = $this->ally->ranks[$u['rank'] - 1]['name'];
			else
				$u["range"] = _getText('Novate');

			$u['points'] = Helpers::pretty_number($u['total_points']);
			$u['time'] = ($u['time'] > 0) ? $this->game->datezone("d.m.Y H:i", $u['time']) : '-';

			$parse['memberslist'][] = $u;

			if ($rank == $u['id'] && $parse['admin'])
			{
				$r['Rank_for'] = 'Установить ранг для ' . $u['username'];
				$r['options'] = "<option value=\"0\">Новичок</option>";

				if (is_array($this->ally->ranks) && count($this->ally->ranks))
				{
					foreach ($this->ally->ranks as $a => $b)
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

		if ($i != $this->ally->members)
			$this->db->query("UPDATE game_alliance SET `members` = '" . $i . "' WHERE `id` = '" . $this->ally->id . "'");

		$parse['i'] = $i;
		$parse['s'] = $s;
		$parse['status'] = $this->ally->canAccess(Alliance::CAN_WATCH_MEMBERLIST_STATUS);

		$this->view->setVar('parse', $parse);

		$this->tag->setTitle(_getText('Members_list'));
		$this->showTopPanel(false);
	}

	public function chatAction ()
	{
		if ($this->user->messages_ally != 0)
			$this->db->query("UPDATE game_users SET `messages_ally` = '0' WHERE `id` = '" . $this->user->id . "'");

		$this->parseInfo($this->user->ally_id);

		if ($this->ally->owner != $this->user->id && !$this->ally->canAccess(Alliance::CHAT_ACCESS))
			$this->message(_getText('Denied_access'), _getText('Send_circular_mail'));

		if (isset($_POST['deletemessages']) && $this->ally->owner == $this->user->id)
		{
			$DeleteWhat = $_POST['deletemessages'];

			if ($DeleteWhat == 'deleteall')
				$this->db->query("DELETE FROM game_alliance_chat WHERE `ally_id` = '" . $this->user->ally_id . "';");
			elseif ($DeleteWhat == 'deletemarked' || $DeleteWhat == 'deleteunmarked')
			{
				$Mess_Array = [];

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
					$this->db->query("DELETE FROM game_alliance_chat WHERE `id` " . (($DeleteWhat == 'deleteunmarked') ? 'NOT' : '') . " IN (" . $Mess_Array . ") AND `ally_id` = '" . $this->user->ally_id . "';");
			}
		}

		if ($this->request->hasPost('text') && $this->request->getPost('text', null, '') != '')
		{
			$this->db->insertAsDict('game_alliance_chat',
			[
				'ally_id' 	=> $this->user->ally_id,
				'user' 		=> $this->user->username,
				'user_id' 	=> $this->user->id,
				'message' 	=> Helpers::FormatText($this->request->getPost('text')),
				'timestamp'	=> time()
			]);

			$this->db->query("UPDATE game_users SET `messages_ally` = `messages_ally` + '1' WHERE `ally_id` = '" . $this->user->ally_id . "' AND id != " . $this->user->id . "");
		}

		$parse = [];
		$parse['messages'] = [];

		$news_count = $this->db->query("SELECT COUNT(*) AS num FROM game_alliance_chat WHERE `ally_id` = '" . $this->user->ally_id . "'")->fetch();

		if ($news_count['num'] > 0)
		{
			$p = (isset($_GET['p'])) ? intval($_GET['p']) : 1;

			$thiss = Helpers::pagination($news_count['num'], 20, '/alliance/chat/', $p);

			$mess = $this->db->query("SELECT * FROM game_alliance_chat WHERE `ally_id` = '" . $this->user->ally_id . "' ORDER BY `id` DESC limit " . (($p - 1) * 20) . ", 20");

			while ($mes = $mess->fetch())
			{
				$parse['messages'][] = $mes;
			}
		}

		$parse['owner'] = ($this->ally->owner == $this->user->id) ? true : false;
		$parse['pages'] = (isset($thiss)) ? $thiss : '[0]';
		$parse['parser'] = $this->user->getUserOption('bb_parser') ? true : false;

		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Альянс-чат');
		$this->showTopPanel(false);
	}

	public function infoAction ($id = '')
	{
		if ($id != '' && !is_numeric($id))
			$allyrow = $this->db->query("SELECT * FROM game_alliance WHERE tag = '" . $id . "'")->fetch();
		elseif ($id > 0 && is_numeric($id))
			$allyrow = $this->db->query("SELECT * FROM game_alliance WHERE id = '" . $id . "'")->fetch();
		else
			$this->message("Указанного альянса не существует в игре!", "Информация об альянсе");

		if (!isset($allyrow['id']))
			$this->message("Указанного альянса не существует в игре!", "Информация об альянсе");

		if ($allyrow['description'] == "")
			$allyrow['description'] = "[center]У этого альянса ещё нет описания[/center]";

		$parse['id'] = $allyrow['id'];
		$parse['member_scount'] = $allyrow['members'];
		$parse['name'] = $allyrow['name'];
		$parse['tag'] = $allyrow['tag'];
		$parse['description'] = $allyrow['description'];
		$parse['image'] = $allyrow['image'];
		$parse['web'] = $allyrow['web'];
		$parse['request'] = ($this->getDI()->has('user') && $this->user->ally_id == 0);

		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Альянс ' . $allyrow['name']);
		$this->showTopPanel(false);
	}

	public function makeAction ()
	{
		if (!$this->auth->isAuthorized())
			$this->message(_getText('Denied_access'), "Ошипко");
			
		$ally_request = $this->db->fetchColumn("SELECT COUNT(*) AS num FROM game_alliance_requests WHERE u_id = " . $this->user->id . ";");

		if ($this->user->ally_id > 0 || $ally_request > 0)
			return $this->indexAction();

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

			$tagquery = $this->db->query("SELECT * FROM game_alliance WHERE tag = '" . addslashes($_POST['atag']) . "'")->fetch();

			if ($tagquery)
				$this->message(str_replace('%s', $_POST['atag'], _getText('always_exist')), _getText('make_alliance'));

			$this->db->query("INSERT INTO game_alliance SET `name` = '" . addslashes($_POST['aname']) . "', `tag`= '" . addslashes($_POST['atag']) . "' , `owner` = '" . $this->user->id . "', `create_time` = " . time());

			$ally_id = $this->db->lastInsertId();

			$this->db->query("UPDATE game_users SET `ally_id` = '" . $ally_id . "', `ally_name` = '" . addslashes($_POST['aname']) . "' WHERE `id` = '" . $this->user->id . "'");
			$this->db->query("INSERT INTO game_alliance_members (a_id, u_id, time) VALUES (" . $ally_id . ", " . $this->user->id . ", " . time() . ")");

			$this->tag->setTitle(_getText('make_alliance'));
			$this->view->setVar('html', $this->MessageForm(str_replace('%s', $_POST['atag'], _getText('ally_maked')), str_replace('%s', $_POST['atag'], _getText('alliance_has_been_maked')) . "<br><br>", "/alliance/", _getText('Ok')));
		}
		else
			$this->tag->setTitle(_getText('make_alliance'));

		$this->showTopPanel(false);

		return true;
	}

	public function searchAction ()
	{
		if (!$this->auth->isAuthorized())
			$this->message(_getText('Denied_access'), "Ошипко");
	
		$ally_request = $this->db->fetchColumn("SELECT COUNT(*) AS num FROM game_alliance_requests WHERE u_id = " . $this->user->id . ";");

		if ($this->user->ally_id > 0 || $ally_request > 0)
			return $this->indexAction();

		$parse = [];

		if (isset($_POST['searchtext']) && $_POST['searchtext'] != '')
		{
			if (!preg_match('/^[a-zA-Zа-яА-Я0-9_\.\,\-\!\?\*\ ]+$/u', $_POST['searchtext']))
				$this->message("Строка поиска содержит запрещённые символы", _getText('make_alliance'), '/alliance/?mode=search', 2);

			$search = $this->db->query("SELECT * FROM game_alliance WHERE name LIKE '%" . $_POST['searchtext'] . "%' or tag LIKE '%" . $_POST['searchtext'] . "%' LIMIT 30");

			$parse['result'] = [];

			if ($search->numRows() != 0)
			{
				while ($s = $search->fetch())
				{
					$entry = [];

					$entry['tag'] = "[<a href=\"/alliance/apply/allyid/".$s['id']."/\">".$s['tag']."</a>]";
					$entry['name'] = $s['name'];
					$entry['members'] = $s['members'];

					$parse['result'][] = $entry;
				}
			}
		}

		$parse['searchtext'] = (isset($_POST['searchtext'])) ? $_POST['searchtext'] : '';

		$this->view->setVar('parse', $parse);

		$this->tag->setTitle(_getText('search_alliance'));
		$this->showTopPanel(false);

		return true;
	}

	public function applyAction ()
	{
		if (!$this->auth->isAuthorized())
			$this->message(_getText('Denied_access'), "Ошипко");
	
		if ($this->user->ally_id > 0)
			return $this->indexAction();

		if (!is_numeric($_GET['allyid']) || !$_GET['allyid'])
			$this->message(_getText('it_is_not_posible_to_apply'), _getText('it_is_not_posible_to_apply'));

		$allyid = intval($_GET['allyid']);

		$allyrow = $this->db->query("SELECT tag, request, request_notallow FROM game_alliance WHERE id = '" . $allyid . "'")->fetch();

		if (!isset($allyrow['tag']))
			$this->message("Альянса не существует!", "Ошибка");

		if ($allyrow['request_notallow'] != 0)
			$this->message("Данный альянс является закрытым для вступлений новых членов", "Ошибка");

		if (isset($_POST['further']))
		{
			$request = $this->db->query("SELECT COUNT(*) AS num FROM game_alliance_requests WHERE a_id = " . $allyid . " AND u_id = " . $this->user->id . ";")->fetch();

			if ($request['num'] == 0)
			{
				$this->db->query("INSERT INTO game_alliance_requests VALUES (" . $allyid . ", " . $this->user->id . ", " . time() . ", '" . strip_tags($_POST['text']) . "')");

				$this->message(_getText('apply_registered'), _getText('your_apply'), '/alliance/', 3);
			}
			else
				$this->message('Вы уже отсылали заявку на вступление в этот альянс!', 'Ошибка', '/alliance/', 3);
		}

		$parse = [];

		$parse['allyid'] = $allyid;
		$parse['text_apply'] = ($allyrow['request']) ? $allyrow['request'] : '';
		$parse['tag'] = $allyrow['tag'];

		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Запрос на вступление');
		$this->showTopPanel(false);

		return true;
	}

	public function statAction ()
	{
		if (!$this->auth->isAuthorized())
			return $this->indexAction();

		$allyid = $this->request->get('id', null, 0);

		$allyrow = $this->db->query("SELECT id, name FROM game_alliance WHERE id = '" . $allyid . "'")->fetch();

		if (!isset($allyrow['id']))
			$this->message('Информация о данном альянсе не найдена');

		$parse = [];
		$parse['name'] = $allyrow['name'];
		$parse['data'] = $this->db->extractResult($this->db->query("SELECT * FROM game_log_stats WHERE id = ".$allyid." AND type = 2 ORDER BY time ASC"));

		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Статистика альянса');
		$this->showTopPanel(false);

		return true;
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