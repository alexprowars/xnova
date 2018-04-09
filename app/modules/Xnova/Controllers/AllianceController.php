<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Friday\Core\Files;
use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\RedirectException;
use Xnova\Format;
use Xnova\Helpers;
use Friday\Core\Lang;
use Xnova\Models\Alliance;
use Xnova\Models\AllianceMember;
use Xnova\Models\Planet;
use Xnova\Request;
use Xnova\User;
use Xnova\Controller;

/**
 * @RoutePrefix("/alliance")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class AllianceController extends Controller
{
	/**
	 * @var \Xnova\Models\Alliance $ally
	 */
	private $ally;

	public function initialize ()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;

		Lang::includeLang('alliance', 'xnova');
	}

	private function parseInfo ($allyId)
	{
		$ally = Alliance::findFirst($allyId);

		if (!$ally)
		{
			$this->db->updateAsDict('game_users', ['ally_id' => 0], 'id = '.$this->user->id);
			$this->db->delete('game_alliance_members', 'u_id = ?', [$this->user->id]);

			throw new RedirectException(_getText('ally_notexist'), _getText('your_alliance'), '/alliance/');
		}

		$this->ally = $ally;
		$this->ally->getMember($this->user->id);
		$this->ally->getRanks();

		if (!$this->ally->member)
		{
			$this->ally->member = new AllianceMember();
			$this->ally->member->a_id = $this->ally->id;
			$this->ally->member->u_id = $this->user->id;
			$this->ally->member->time = time();

			$this->ally->member->create();
		}
	}

	private function noAlly ()
	{
		if ($this->request->hasPost('bcancel') && $this->request->hasPost('r_id'))
		{
			$this->db->query("DELETE FROM game_alliance_requests WHERE a_id = " . intval($this->request->getPost('r_id')) . " AND u_id = " . $this->user->id);

			throw new RedirectException("Вы отозвали свою заявку на вступление в альянс", "Отзыв заявки", "/alliance/", 2);
		}

		$parse = [];

		$parse['list'] = [];

		$requests = $this->db->query("SELECT r.*, a.name, a.tag FROM game_alliance_requests r LEFT JOIN game_alliance a ON a.id = r.a_id WHERE r.u_id = " . $this->user->id . ";");

		while ($request = $requests->fetch())
			$parse['list'][] = [$request['a_id'], $request['tag'], $request['name'], $request['time']];

		$parse['allys'] = [];

		$allys = $this->db->query("SELECT s.total_points, a.id, a.tag, a.name, a.members FROM game_statpoints s, game_alliance a WHERE s.stat_type = '2' AND s.stat_code = '1' AND a.id = s.id_owner ORDER BY s.total_points DESC LIMIT 0,15;");

		while ($ally = $allys->fetch())
		{
			$ally['total_points'] = Format::number($ally['total_points']);
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
			throw new ErrorException(_getText('Denied_access'), "Ошипко");
	
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

			$parse['range'] = $range;

			$parse['diplomacy'] = false;
	
			if ($this->ally->canAccess(Alliance::DIPLOMACY_ACCESS))
				$parse['diplomacy'] = $this->db->fetchColumn("SELECT count(*) FROM game_alliance_diplomacy WHERE d_id = :id AND status = 0", ['id' => $this->ally->id]);

			$parse['requests'] = 0;

			if ($this->ally->owner == $this->user->id || $this->ally->canAccess(Alliance::REQUEST_ACCESS))
				$parse['requests'] = $this->db->fetchColumn("SELECT COUNT(*) AS num FROM game_alliance_requests WHERE a_id = :id", ['id' => $this->ally->id]);

			$parse['alliance_admin'] = $this->ally->canAccess(Alliance::ADMIN_ACCESS);
			$parse['chat_access'] = $this->ally->canAccess(Alliance::CHAT_ACCESS);
			$parse['members_list'] = $this->ally->canAccess(Alliance::CAN_WATCH_MEMBERLIST);
			$parse['owner'] = ($this->ally->owner != $this->user->id) ? $this->MessageForm(_getText('Exit_of_this_alliance'), "", "/alliance/exit/", _getText('Continue')) : '';

			$parse['image'] = '';

			if ((int) $this->ally->image > 0)
			{
				$image = Files::getById($this->ally->image);

				if ($image)
					$parse['image'] = $image['src'];
			}

			$parse['description'] = str_replace(["\r\n", "\n", "\r"], '', stripslashes($this->ally->description));
			$parse['text'] = str_replace(["\r\n", "\n", "\r"], '', stripslashes($this->ally->text));

			$parse['web'] = $this->ally->web;

			if ($parse['web'] != '' && strpos($parse['web'], 'http') === false)
				$parse['web'] = 'http://'.$parse['web'];

			$parse['tag'] = $this->ally->tag;
			$parse['members'] = $this->ally->members;
			$parse['name'] = $this->ally->name;

			$this->view->setVar('parse', $parse);

			$this->tag->setTitle('Ваш альянс');
			$this->showTopPanel(false);
		}
	}

	public function adminAction ()
	{
		$edit = $this->request->getQuery('edit', null, '');

		$this->parseInfo($this->user->ally_id);

		if ($this->user->isAdmin() && $edit == 'planets')
		{
			if ($this->ally->owner != $this->user->id)
				throw new ErrorException(_getText('Denied_access'), "Управление планетами");

			$parse = [];

			$parse['list'] = $this->db->extractResult($this->db->query("SELECT id, id_ally, name, galaxy, system, planet FROM game_planets WHERE planet_type = 5 AND id_owner = ".$this->user->id.""));
			$parse['credits'] = $this->user->credits;

			$parse['bases'] = Planet::count(['planet_type = 5 AND id_ally = ?0', 'bind' => [$this->ally->id]]);

			$parse['need'] = 100 * ($parse['bases'] > 0 ? (5 + ((int) $parse['bases'] - 1) * 5) : 1);

			if ($this->request->hasQuery('ally'))
			{
				$id = $this->request->getQuery('ally', 'int', 0);

				$check = $this->db->query("SELECT id, id_ally FROM game_planets WHERE planet_type = 5 AND id_ally = 0 AND id_owner = ".$this->user->id." AND id = ".$id."")->fetch();

				if (isset($check['id']))
				{
					if ($this->user->credits >= $parse['need'])
					{
						$this->db->updateAsDict('game_planets', ['id_ally' => $this->ally->id, 'name' => $this->ally->name], 'id = '.$check['id']);

						$this->user->saveData(['-credits' => $parse['need']]);

						throw new RedirectException("Планета была успешно преобразована", "Управление планетами", "/alliance/admin/edit/planets/", 3);
					}
					else
						throw new ErrorException("Недостаточно кредитов для преобразования планеты", "Управление планетами");
				}
			}

			$this->view->pick('alliance/planets');
			$this->view->setVar('parse', $parse);

			$this->tag->setTitle('Управление планетами');
			$this->showTopPanel(false);
		}

		if ($edit == 'rights')
		{
			if (!$this->ally->canAccess(Alliance::CAN_EDIT_RIGHTS) && !$this->user->isAdmin())
				throw new ErrorException(_getText('Denied_access'), _getText('Members_list'));
			elseif (!empty($this->request->getPost('newrangname')))
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

				$this->db->query("UPDATE game_alliance SET ranks = '" . addslashes(json_encode($this->ally->ranks)) . "' WHERE id = " . $this->ally->id);
			}
			elseif ($this->request->hasPost('id') && is_array($this->request->getPost('id')))
			{
				$ally_ranks_new = [];

				foreach ($this->request->getPost('id') as $id)
				{
					$name = $this->ally->ranks[$id]['name'];

					$ally_ranks_new[$id]['name'] = $name;

					$ally_ranks_new[$id][Alliance::CAN_DELETE_ALLIANCE] = ($this->ally->owner == $this->user->id ? ($this->request->hasPost('u' . $id . 'r0') ? 1 : 0) : $this->ally->ranks[$id][Alliance::CAN_DELETE_ALLIANCE]);
					$ally_ranks_new[$id][Alliance::CAN_KICK] = ($this->ally->owner == $this->user->id ? ($this->request->hasPost('u' . $id . 'r1') ? 1 : 0) : $this->ally->ranks[$id][Alliance::CAN_KICK]);
					$ally_ranks_new[$id][Alliance::REQUEST_ACCESS] = $this->request->hasPost('u' . $id . 'r2') ? 1 : 0;
					$ally_ranks_new[$id][Alliance::CAN_WATCH_MEMBERLIST] = $this->request->hasPost('u' . $id . 'r3') ? 1 : 0;
					$ally_ranks_new[$id][Alliance::CAN_ACCEPT] = $this->request->hasPost('u' . $id . 'r4') ? 1 : 0;
					$ally_ranks_new[$id][Alliance::ADMIN_ACCESS] = $this->request->hasPost('u' . $id . 'r5') ? 1 : 0;
					$ally_ranks_new[$id][Alliance::CAN_WATCH_MEMBERLIST_STATUS] = $this->request->hasPost('u' . $id . 'r6') ? 1 : 0;
					$ally_ranks_new[$id][Alliance::CHAT_ACCESS] = $this->request->hasPost('u' . $id . 'r7') ? 1 : 0;
					$ally_ranks_new[$id][Alliance::CAN_EDIT_RIGHTS] = $this->request->hasPost('u' . $id . 'r8') ? 1 : 0;
					$ally_ranks_new[$id][Alliance::DIPLOMACY_ACCESS] = $this->request->hasPost('u' . $id . 'r9') ? 1 : 0;
					$ally_ranks_new[$id][Alliance::PLANET_ACCESS] = $this->request->hasPost('u' . $id . 'r10') ? 1 : 0;
				}

				$this->ally->ranks = $ally_ranks_new;

				$this->db->query("UPDATE game_alliance SET ranks = '" . addslashes(json_encode($this->ally->ranks)) . "' WHERE id = " . $this->ally->id);
			}
			elseif ($this->request->hasQuery('d') && isset($this->ally->ranks[$this->request->getQuery('d', 'int')]))
			{
				unset($this->ally->ranks[$this->request->getQuery('d', 'int')]);

				$this->db->query("UPDATE game_alliance SET ranks = '" . addslashes(json_encode($this->ally->ranks)) . "' WHERE id = " . $this->ally->id);
			}

			$parse['list'] = [];

			if (is_array($this->ally->ranks) && count($this->ally->ranks) > 0)
			{
				foreach ($this->ally->ranks as $a => $b)
				{
					$list['id'] = $a;
					$list['delete'] = "<a href=\"".$this->url->get('alliance/admin/edit/rights/d/'.$a.'/')."\"><img src=\"".$this->url->getStatic('assets/images/abort.gif')."\" alt=\"Удалить ранг\" border=0></a>";
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
			if (!$this->ally->canAccess(Alliance::ADMIN_ACCESS))
				throw new ErrorException(_getText('Denied_access'), "Меню управления альянсом");

			$t = (int) $this->request->getQuery('t', 'int', 1);

			if ($t != 1 && $t != 2 && $t != 3)
				$t = 1;

			if ($this->request->hasPost('options'))
			{
				$this->ally->owner_range = Helpers::checkString($this->request->getPost('owner_range', 'string', ''), true);
				$this->ally->web = Helpers::checkString($this->request->getPost('web', 'string', ''), true);

				if ($this->request->hasFiles())
				{
					/** @var $files \Phalcon\Http\Request\File[] */
					$files = $this->request->getUploadedFiles();

					foreach ($files as $file)
					{
						if ($file->isUploadedFile() && $file->getKey() == 'image')
						{
							$fileType = $file->getRealType();

							if (strpos($fileType, 'image/') === false)
								throw new ErrorException('Разрешены к загрузке только изображения');

							$this->ally->image = Files::save($file);
						}
					}
				}

				if ($this->request->getPost('delete_image'))
					Files::delete($this->ally->image);

				$this->ally->request_notallow = (int) $this->request->getPost('request_notallow', 'int', 0);

				if ($this->ally->request_notallow != 0 && $this->ally->request_notallow != 1)
					throw new ErrorException("Недопустимое значение атрибута!", "Ошибка");

				$this->ally->update();
			}
			elseif ($this->request->hasPost('t'))
			{
				if ($t == 3)
					$this->ally->request = Format::text($this->request->getPost('text', 'string', ''));
				elseif ($t == 2)
					$this->ally->text = Format::text($this->request->getPost('text', 'string', ''));
				else
					$this->ally->description = Format::text($this->request->getPost('text', 'string', ''));

				$this->ally->update();
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

			$parse['image'] = '';

			if ((int) $this->ally->image > 0)
			{
				$image = Files::getById($this->ally->image);

				if ($image)
					$parse['image'] = $image['src'];
			}

			$parse['request_allow'] = $this->ally->request_notallow;
			$parse['owner_range'] = $this->ally->owner_range;
			
			$parse['can_view_members'] = $this->ally->canAccess(Alliance::CAN_KICK);

			if ($this->ally->owner == $this->user->id)
				$parse['Transfer_alliance'] = $this->MessageForm("Покинуть / Передать альянс", "", "/alliance/admin/edit/give/", 'Продолжить');

			if ($this->ally->canAccess(Alliance::CAN_DELETE_ALLIANCE))
				$parse['Disolve_alliance'] = $this->MessageForm("Расформировать альянс", "", "/alliance/admin/edit/exit/", 'Продолжить');

			$this->view->pick('alliance/admin');
			$this->view->setVar('parse', $parse);

			$this->tag->setTitle(_getText('Alliance_admin'));
			$this->showTopPanel(false);
		}
		elseif ($edit == 'requests')
		{
			if ($this->ally->owner != $this->user->id && !$this->ally->canAccess(Alliance::CAN_ACCEPT) && !$this->ally->canAccess(Alliance::REQUEST_ACCESS))
				throw new ErrorException(_getText('Denied_access'), _getText('Check_the_requests'));

			if (($this->ally->owner == $this->user->id || $this->ally->canAccess(Alliance::CAN_ACCEPT)) && $this->request->hasPost('action'))
			{
				$show = $this->request->getQuery('show', 'int', 0);

				if ($this->request->getPost('action') == "Принять")
				{
					if ($this->ally->members >= 150)
						throw new ErrorException('Альянс не может иметь больше 150 участников', _getText('Check_the_requests'));
					else
					{
						if ($this->request->getPost('text') != '')
							$text_ot = strip_tags($this->request->getPost('text'));

						$check = $this->db->query("SELECT a_id FROM game_alliance_requests WHERE a_id = " . $this->ally->id . " AND u_id = " . $show . "")->fetch();

						if (isset($check['a_id']))
						{
							$this->db->delete('game_alliance_requests', "u_id = ?", [$show]);
							$this->db->delete('game_alliance_members', "u_id = ?", [$show]);

							$this->db->insertAsDict('game_alliance_members', ['a_id' => $this->ally->id, 'u_id' => $show, 'time' => time()]);

							$this->db->execute("UPDATE game_alliance SET members = members + 1 WHERE id = ?", [$this->ally->id]);
							$this->db->query("UPDATE game_users SET ally_name = '" . $this->ally->name . "', ally_id = '" . $this->ally->id . "' WHERE id = '" . $show . "'");

							User::sendMessage($show, $this->user->id, 0, 2, $this->ally->tag, "Привет!<br>Альянс <b>" . $this->ally->name . "</b> принял вас в свои ряды!" . ((isset($text_ot)) ? "<br>Приветствие:<br>" . $text_ot . "" : ""));

							return $this->response->redirect("alliance/members/");
						}
					}
				}
				elseif ($this->request->getPost('action') == "Отклонить")
				{
					if ($this->request->getPost('text') != '')
						$text_ot = strip_tags($this->request->getPost('text'));

					$this->db->delete('game_alliance_requests', "u_id = ? AND a_id = ?", [$show, $this->ally->id]);

					User::sendMessage($show, $this->user->id, 0, 2, $this->ally->tag, "Привет!<br>Альянс <b>" . $this->ally->name . "</b> отклонил вашу кандидатуру!" . ((isset($text_ot)) ? "<br>Причина:<br>" . $text_ot . "" : ""));
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
			if (!$this->ally->canAccess(Alliance::ADMIN_ACCESS))
				throw new ErrorException(_getText('Denied_access'), _getText('Members_list'));

			if ($this->request->hasPost('newname'))
			{
				$name = $this->request->getPost('newname', 'string', '');

				if (!preg_match("/^[a-zA-Zа-яА-Я0-9_\.\,\-\!\?\*\ ]+$/u", $name))
					throw new ErrorException("Название альянса содержит запрещённые символы", _getText('make_alliance'));

				$this->ally->name = addslashes(htmlspecialchars($name));
				$this->ally->update();

				$this->db->query("UPDATE game_users SET ally_name = '" . $this->ally->name . "' WHERE ally_id = '" . $this->ally->id . "';");
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
			if (!$this->ally->canAccess(Alliance::ADMIN_ACCESS))
				throw new ErrorException(_getText('Denied_access'), _getText('Members_list'));

			if ($this->request->hasPost('newtag'))
			{
				$tag = $this->request->getPost('newtag', 'string', '');

				if (!preg_match('/^[a-zA-Zа-яА-Я0-9_\.\,\-\!\?\*\ ]+$/u', $tag))
					throw new ErrorException("Абревиатура альянса содержит запрещённые символы", _getText('make_alliance'));

				$this->ally->tag = addslashes(htmlspecialchars($tag));
				$this->ally->update();
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
				throw new ErrorException(_getText('Denied_access'), _getText('Members_list'));

			$this->ally->deleteAlly();

			return $this->response->redirect('alliance/');
		}
		elseif ($edit == 'give')
		{
			if ($this->ally->owner != $this->user->id)
				throw new RedirectException("Доступ запрещён.", "Ошибка!", "/alliance/", 2);

			if ($this->request->hasPost('newleader') && $this->ally->owner == $this->user->id)
			{
				$info = $this->db->query("SELECT id, ally_id FROM game_users WHERE id = '" . $this->request->getPost('newleader', 'int') . "'")->fetch();

				if (!$info['id'] || $info['ally_id'] != $this->user->ally_id)
					throw new RedirectException("Операция невозможна.", "Ошибка!", "/alliance/", 2);

				$this->db->query("UPDATE game_alliance SET owner = '" . $info['id'] . "' WHERE id = " . $this->user->ally_id . " ");
				$this->db->query("UPDATE game_alliance_members SET rank = '0' WHERE u_id = '" . $info['id'] . "';");

				return $this->response->redirect('alliance/');
			}

			$listuser = $this->db->query("SELECT u.username, u.id, m.rank FROM game_users u LEFT JOIN game_alliance_members m ON m.u_id = u.id WHERE u.ally_id = '" . $this->user->ally_id . "' AND u.id != " . $this->ally->owner . " AND m.rank != 0;");

			$parse['righthand'] = '';

			while ($u = $listuser->fetch())
			{
				if ($this->ally->ranks[$u['rank'] - 1][Alliance::CAN_EDIT_RIGHTS] == 1)
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
				throw new ErrorException(_getText('Denied_access'), _getText('Members_list'));

			if ($this->request->hasQuery('kick'))
			{
				$kick = $this->request->getQuery('kick', 'int', 0);

				if ($this->ally->owner != $this->user->id && !$this->ally->canAccess(Alliance::CAN_KICK) && $kick > 0)
					throw new ErrorException(_getText('Denied_access'), _getText('Members_list'));

				$u = $this->db->query("SELECT * FROM game_users WHERE id = '" . $kick . "' LIMIT 1")->fetch();

				if ($u['ally_id'] == $this->ally->id && $u['id'] != $this->ally->owner)
				{
					$this->db->query("UPDATE game_planets SET id_ally = 0 WHERE id_owner = ".$u['id']." AND id_ally = ".$this->ally->id."");

					$this->db->query("UPDATE game_users SET ally_id = '0', ally_name = '' WHERE id = '" . $u['id'] . "'");
					$this->db->query("DELETE FROM game_alliance_members WHERE u_id = " . $u['id'] . ";");
				}
				else
					throw new ErrorException(_getText('Denied_access'), _getText('Members_list'));
			}
			elseif ($this->request->getPost('newrang', null, '') != '' && $this->request->get('id', 'int', 0) != 0)
			{
				$id = $this->request->get('id', 'int', 0);
				$rank = $this->request->getPost('newrang', 'int', 0);

				$q = $this->db->query("SELECT id, ally_id FROM game_users WHERE id = '" . $id . "' LIMIT 1")->fetch();

				if ((isset($this->ally->ranks[$rank - 1]) || $rank == 0) && $q['id'] != $this->ally->owner && $q['ally_id'] == $this->ally->id)
					$this->db->query("UPDATE game_alliance_members SET rank = '" . $rank . "' WHERE u_id = '" . $id . "';");
			}

			$this->membersAction();
		}
		else
			return $this->response->redirect('alliance/');

		return true;
	}

	public function diplomacyAction ()
	{
		$this->parseInfo($this->user->ally_id);

		if ($this->ally->owner != $this->user->id && !$this->ally->canAccess(Alliance::DIPLOMACY_ACCESS))
			throw new ErrorException(_getText('Denied_access'), "Дипломатия");

		$parse['DText'] = $parse['DMyQuery'] = $parse['DQuery'] = [];

		if ($this->request->hasQuery('edit'))
		{
			if ($this->request->getQuery('edit', null, '') == "add")
			{
				$st = (int) $this->request->getPost('status', 'int', 0);
				$al = $this->db->query("SELECT id, name FROM game_alliance WHERE id = '" . intval($this->request->getPost('ally')) . "'")->fetch();

				if (!$al['id'])
					throw new RedirectException("Ошибка ввода параметров", "Дипломатия", "/alliance/diplomacy/", 3);

				$ad = $this->db->query("SELECT id FROM game_alliance_diplomacy WHERE a_id = " . $this->ally->id . " AND d_id = " . $al['id'] . ";");

				if ($ad->numRows() > 0)
					throw new RedirectException("У вас уже есть соглашение с этим альянсом. Разорвите старое соглашения прежде чем создать новое.", "Дипломатия", "/alliance/diplomacy/", 3);

				if ($st < 0 || $st > 3)
					$st = 0;

				$this->db->query("INSERT INTO game_alliance_diplomacy VALUES (NULL, " . $this->ally->id . ", " . $al['id'] . ", " . $st . ", 0, 1)");
				$this->db->query("INSERT INTO game_alliance_diplomacy VALUES (NULL, " . $al['id'] . ", " . $this->ally->id . ", " . $st . ", 0, 0)");

				throw new RedirectException("Отношение между вашими альянсами успешно добавлено", "Дипломатия", "/alliance/diplomacy/", 3);
			}
			elseif ($this->request->getQuery('edit', null, '') == "del")
			{
				$al = $this->db->query("SELECT a_id, d_id FROM game_alliance_diplomacy WHERE id = '" . intval($_GET['id']) . "' AND a_id = " . $this->ally->id . ";")->fetch();

				if (!$al['a_id'])
					throw new RedirectException("Ошибка ввода параметров", "Дипломатия", "/alliance/diplomacy/", 3);

				$this->db->query("DELETE FROM game_alliance_diplomacy WHERE a_id = " . $al['a_id'] . " AND d_id = " . $al['d_id'] . ";");
				$this->db->query("DELETE FROM game_alliance_diplomacy WHERE a_id = " . $al['d_id'] . " AND d_id = " . $al['a_id'] . ";");

				throw new RedirectException("Отношение между вашими альянсами расторжено", "Дипломатия", "/alliance/diplomacy/", 3);
			}
			elseif ($this->request->getQuery('edit', null, '') == "suc")
			{
				$al = $this->db->query("SELECT a_id, d_id FROM game_alliance_diplomacy WHERE id = '" . intval($_GET['id']) . "' AND a_id = " . $this->ally->id . "")->fetch();

				if (!$al['a_id'])
					throw new RedirectException("Ошибка ввода параметров", "Дипломатия", "/alliance/diplomacy/", 3);

				$this->db->query("UPDATE game_alliance_diplomacy SET status = 1 WHERE a_id = " . $al['a_id'] . " AND d_id = " . $al['d_id'] . ";");
				$this->db->query("UPDATE game_alliance_diplomacy SET status = 1 WHERE a_id = " . $al['d_id'] . " AND d_id = " . $al['a_id'] . ";");

				throw new RedirectException("Отношение между вашими альянсами подтверждено", "Дипломатия", "/alliance/diplomacy/", 3);
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
		$this->parseInfo($this->user->ally_id);

		if ($this->ally->owner == $this->user->id)
			throw new ErrorException(_getText('Owner_cant_go_out'), _getText('Alliance'));

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
				throw new ErrorException(_getText('Denied_access'), _getText('Members_list'));

			$parse['admin'] = false;
		}

		$sort1 = $this->request->getQuery('sort1', 'int', 0);
		$sort2 = $this->request->getQuery('sort2', 'int', 0);

		$rank = $this->request->getQuery('rank', 'int', 0);

		$sort = "";

		if ($sort2)
		{
			if ($sort1 == 1)
				$sort = " ORDER BY u.username";
			elseif ($sort1 == 2)
				$sort = " ORDER BY m.rank";
			elseif ($sort1 == 3)
				$sort = " ORDER BY s.total_points";
			elseif ($sort1 == 4)
				$sort = " ORDER BY m.time";
			elseif ($sort1 == 5 && $this->ally->canAccess(Alliance::CAN_WATCH_MEMBERLIST_STATUS))
				$sort = " ORDER BY u.onlinetime";
			else
				$sort = " ORDER BY u.id";

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
				$u["onlinetime"] = "<span class='positive'>" . _getText('On') . "</span>";
			elseif ($u["onlinetime"] + 60 * 20 >= time() && $this->ally->canAccess(Alliance::CAN_WATCH_MEMBERLIST_STATUS))
				$u["onlinetime"] = "<span class='neutral'>" . _getText('15_min') . "</span>";
			elseif ($this->ally->canAccess(Alliance::CAN_WATCH_MEMBERLIST_STATUS))
			{
				$hours = floor((time() - $u["onlinetime"]) / 3600);

				$u["onlinetime"] = "<span class='negative'>"._getText('Off')." ".Format::time($hours * 3600)."</span>";
			}

			if ($this->ally->owner == $u['id'])
				$u["range"] = ($this->ally->owner_range == '') ? "Основатель" : $this->ally->owner_range;
			elseif (isset($this->ally->ranks[$u['rank'] - 1]['name']))
				$u["range"] = $this->ally->ranks[$u['rank'] - 1]['name'];
			else
				$u["range"] = _getText('Novate');

			$u['points'] = Format::number($u['total_points']);
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
			$this->db->query("UPDATE game_alliance SET members = '" . $i . "' WHERE id = '" . $this->ally->id . "'");

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
		{
			$this->user->messages_ally = 0;
			$this->user->update();
		}
		
		$this->parseInfo($this->user->ally_id);

		if ($this->ally->owner != $this->user->id && !$this->ally->canAccess(Alliance::CHAT_ACCESS))
			throw new ErrorException(_getText('Denied_access'), _getText('Send_circular_mail'));

		if ($this->request->hasPost('delete_type') && $this->ally->owner == $this->user->id)
		{
			$deleteType = $this->request->getPost('delete_type');

			if ($deleteType == 'all')
				$this->db->query("DELETE FROM game_alliance_chat WHERE ally_id = :ally", ['ally' => $this->user->ally_id]);
			elseif ($deleteType == 'marked' || $deleteType == 'unmarked')
			{
				$messages = $this->request->getPost('delete');
				$messages = array_map('intval', $messages);

				if (count($messages))
					$this->db->query("DELETE FROM game_alliance_chat WHERE id " . (($deleteType == 'unmarked') ? 'NOT' : '') . " IN (" . implode(',', $messages) . ") AND ally_id = :ally", ['ally' => $this->user->ally_id]);
			}
		}

		if ($this->request->hasPost('text') && $this->request->getPost('text', null, '') != '')
		{
			$this->db->insertAsDict('game_alliance_chat',
			[
				'ally_id' 	=> $this->user->ally_id,
				'user' 		=> $this->user->username,
				'user_id' 	=> $this->user->id,
				'message' 	=> Format::text($this->request->getPost('text')),
				'timestamp'	=> time()
			]);

			$this->db->query("UPDATE game_users SET messages_ally = messages_ally + '1' WHERE ally_id = '" . $this->user->ally_id . "' AND id != " . $this->user->id . "");

			$this->response->redirect('alliance/chat/');
		}

		$parse = [];
		$parse['items'] = [];

		$messagesCount = $this->db->query("SELECT COUNT(*) AS num FROM game_alliance_chat WHERE ally_id = ?", [$this->user->ally_id])->fetch()['num'];

		$parse['pagination'] = [
			'total' => (int) $messagesCount,
			'limit' => 10,
			'page' => (int) $this->request->getQuery('p', 'int', 1)
		];

		if ($messagesCount > 0)
		{
			$mess = $this->db->query("SELECT * FROM game_alliance_chat WHERE ally_id = '" . $this->user->ally_id . "' ORDER BY id DESC limit " . (($parse['pagination']['page'] - 1) * $parse['pagination']['limit']) . ", ".$parse['pagination']['limit']."");

			while ($mes = $mess->fetch())
			{
				$parse['items'][] = [
					'id' => (int) $mes['id'],
					'user' => $mes['user'],
					'user_id' => (int) $mes['user_id'],
					'time' => (int) $mes['timestamp'],
					'text' => str_replace(["\r\n", "\n", "\r"], '', stripslashes($mes['message'])),
				];
			}
		}



		$parse['owner'] = ($this->ally->owner == $this->user->id) ? true : false;
		$parse['parser'] = $this->user->getUserOption('bb_parser') ? true : false;

		Request::addData('page', $parse);

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
			throw new ErrorException("Указанного альянса не существует в игре!", "Информация об альянсе");

		if (!isset($allyrow['id']))
			throw new ErrorException("Указанного альянса не существует в игре!", "Информация об альянсе");

		if ($allyrow['description'] == "")
			$allyrow['description'] = "[center]У этого альянса ещё нет описания[/center]";

		$parse['id'] = $allyrow['id'];
		$parse['member_scount'] = $allyrow['members'];
		$parse['name'] = $allyrow['name'];
		$parse['tag'] = $allyrow['tag'];
		$parse['description'] = str_replace(["\r\n", "\n", "\r"], '', stripslashes($allyrow['description']));
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
			throw new ErrorException(_getText('Denied_access'), "Ошипко");
			
		$ally_request = $this->db->fetchColumn("SELECT COUNT(*) AS num FROM game_alliance_requests WHERE u_id = " . $this->user->id . ";");

		if ($this->user->ally_id > 0 || $ally_request > 0)
			return $this->indexAction();

		if ($this->request->hasQuery('yes') && $this->request->isPost())
		{
			$tag = $this->request->getPost('atag', 'string', '');
			$name = $this->request->getPost('aname', 'string', '');

			if ($tag == '')
				throw new ErrorException(_getText('have_not_tag'), _getText('make_alliance'));
			if ($name == '')
				throw new ErrorException(_getText('have_not_name'), _getText('make_alliance'));
			if (!preg_match('/^[a-zA-Zа-яА-Я0-9_\.\,\-\!\?\*\ ]+$/u', $tag))
				throw new ErrorException("Абревиатура альянса содержит запрещённые символы", _getText('make_alliance'));
			if (!preg_match('/^[a-zA-Zа-яА-Я0-9_\.\,\-\!\?\*\ ]+$/u', $name))
				throw new ErrorException("Название альянса содержит запрещённые символы", _getText('make_alliance'));

			$find = $this->db->query("SELECT id FROM game_alliance WHERE tag = :tag", ['tag' => addslashes($tag)])->fetch();

			if ($find)
				throw new ErrorException(str_replace('%s', $tag, _getText('always_exist')), _getText('make_alliance'));

			$alliance = new Alliance();

			$alliance->name = addslashes($name);
			$alliance->tag = addslashes($tag);
			$alliance->owner = $this->user->id;
			$alliance->create_time = time();

			if (!$alliance->create())
				throw new ErrorException('Произошла ошибка при создании альянса');

			$member = new AllianceMember();

			$member->a_id = $alliance->id;
			$member->u_id = $this->user->id;
			$member->time = time();

			if (!$member->create())
				throw new ErrorException('Произошла ошибка при создании альянса');

			$this->user->ally_id = $alliance->id;
			$this->user->ally_name = $alliance->name;
			$this->user->update();

			$this->tag->setTitle(_getText('make_alliance'));
			$this->view->setVar('html', $this->MessageForm(str_replace('%s', $alliance->tag, _getText('ally_maked')), str_replace('%s', $alliance->tag, _getText('alliance_has_been_maked')), "/alliance/", _getText('Ok')));
		}
		else
			$this->tag->setTitle(_getText('make_alliance'));

		$this->showTopPanel(false);

		return true;
	}

	public function searchAction ()
	{
		if (!$this->auth->isAuthorized())
			throw new ErrorException(_getText('Denied_access'), "Ошипко");
	
		$ally_request = $this->db->fetchColumn("SELECT COUNT(*) AS num FROM game_alliance_requests WHERE u_id = " . $this->user->id . ";");

		if ($this->user->ally_id > 0 || $ally_request > 0)
			return $this->indexAction();

		$parse = [];

		$text = '';

		if ($this->request->hasPost('searchtext') && $this->request->getPost('searchtext') != '')
		{
			$text = $this->request->getPost('searchtext');

			if (!preg_match('/^[a-zA-Zа-яА-Я0-9_\.\,\-\!\?\*\ ]+$/u', $text))
				throw new RedirectException("Строка поиска содержит запрещённые символы", _getText('make_alliance'), '/alliance/search/', 2);

			$search = $this->db->query("SELECT * FROM game_alliance WHERE name LIKE '%" . $text . "%' or tag LIKE '%" . $text . "%' LIMIT 30");

			$parse['result'] = [];

			if ($search->numRows() != 0)
			{
				while ($s = $search->fetch())
				{
					$entry = [];

					$entry['tag'] = "[<a href=\"".$this->url->get('alliance/apply/allyid/'.$s['id'].'/')."\">".$s['tag']."</a>]";
					$entry['name'] = $s['name'];
					$entry['members'] = $s['members'];

					$parse['result'][] = $entry;
				}
			}
		}

		$parse['searchtext'] = $text;

		$this->view->setVar('parse', $parse);

		$this->tag->setTitle(_getText('search_alliance'));
		$this->showTopPanel(false);

		return true;
	}

	public function applyAction ()
	{
		if (!$this->auth->isAuthorized())
			throw new ErrorException(_getText('Denied_access'), "Ошипко");
	
		if ($this->user->ally_id > 0)
			return $this->indexAction();

		$allyid = $this->request->getQuery('allyid', 'int', 0);

		if ($allyid <= 0)
			throw new ErrorException(_getText('it_is_not_posible_to_apply'), _getText('it_is_not_posible_to_apply'));

		$allyrow = $this->db->query("SELECT tag, request, request_notallow FROM game_alliance WHERE id = '" . $allyid . "'")->fetch();

		if (!isset($allyrow['tag']))
			throw new ErrorException("Альянса не существует!", "Ошибка");

		if ($allyrow['request_notallow'] != 0)
			throw new ErrorException("Данный альянс является закрытым для вступлений новых членов", "Ошибка");

		if ($this->request->hasPost('further'))
		{
			$request = $this->db->query("SELECT COUNT(*) AS num FROM game_alliance_requests WHERE a_id = " . $allyid . " AND u_id = " . $this->user->id . ";")->fetch();

			if ($request['num'] == 0)
			{
				$this->db->query("INSERT INTO game_alliance_requests VALUES (" . $allyid . ", " . $this->user->id . ", " . time() . ", '" . strip_tags($this->request->getPost('text')) . "')");

				throw new RedirectException(_getText('apply_registered'), _getText('your_apply'), '/alliance/', 3);
			}
			else
				throw new RedirectException('Вы уже отсылали заявку на вступление в этот альянс!', 'Ошибка', '/alliance/', 3);
		}

		$parse = [];

		$parse['allyid'] = $allyid;
		$parse['text_apply'] = ($allyrow['request']) ? str_replace(["\r\n", "\n", "\r"], '', stripslashes($allyrow['request'])) : '';
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
			throw new ErrorException('Информация о данном альянсе не найдена');

		$parse = [];
		$parse['name'] = $allyrow['name'];
		$parse['points'] = [];

		$items = $this->db->query("SELECT * FROM game_log_stats WHERE object_id = ".$allyid." AND time > ".(time() - 14 * 86400)." AND type = 2 ORDER BY time ASC");

		while ($item = $items->fetch())
		{
			$parse['points'][] = [
				'date' => (int) $item['time'],
				'rank' => [
					'tech' => (int) $item['tech_rank'],
					'build' => (int) $item['build_rank'],
					'defs' => (int) $item['defs_rank'],
					'fleet' => (int) $item['fleet_rank'],
					'total' => (int) $item['total_rank'],
				],
				'point' => [
					'tech' => (int) $item['tech_points'],
					'build' => (int) $item['build_points'],
					'defs' => (int) $item['defs_points'],
					'fleet' => (int) $item['fleet_points'],
					'total' => (int) $item['total_points'],
				]
			];
		}

		Request::addData('page', $parse);

		$this->tag->setTitle('Статистика альянса');
		$this->showTopPanel(false);

		return true;
	}

	private function MessageForm ($Title, $Message, $Goto = '', $Button = ' ok ', $TwoLines = false)
	{
		$Form = "<form action=\"" . $this->url->get(ltrim($Goto, '/')) . "\" method=\"post\">";
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