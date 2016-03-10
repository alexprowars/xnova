<?php

namespace App\Controllers;

class BuddyController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();
	}

	public function newAction ($userId)
	{
		$u = $this->db->query("SELECT id, username FROM game_users WHERE id = '" . intval($userId) . "'")->fetch();

		if (isset($u['id']))
		{
			if ($this->request->isPost())
			{
				$buddy = $this->db->query("SELECT id FROM game_buddy WHERE sender = ".$this->user->id." AND owner = ".$userId." OR sender = ".$userId." AND owner = ".$this->user->id."")->fetch();

				if (!isset($buddy['id']))
				{
					if (mb_strlen($this->request->getPost('text', 'string', ''), 'UTF-8') > 5000)
						$this->message("Максимальная длинна сообщения 5000 символов!", "Ошибка");

					$this->db->insertAsDict('game_buddy',
					[
						'sender'	=> $this->user->id,
						'owner'		=> $u['id'],
						'active'	=> 0,
						'text'		=> strip_tags($this->request->getPost('text', 'string', ''))
					]);

					$this->game->sendMessage($u['id'], 0, time(), 1, 'Запрос дружбы', 'Игрок '.$this->user->username.' отправил вам запрос на добавление в друзья. <a href="/buddy/requests/"><< просмотреть >></a>');

					$this->message('Запрос отправлен', 'Предложение дружбы', '/buddy/');
				}
				else
					$this->message('Запрос дружбы был уже отправлен ранее', 'Предложение дружбы');
			}

			if ($u['id'] != $this->user->id)
			{
				$parse = $u;

				$this->view->setVar('parse', $parse);

				$this->tag->setTitle('Друзья');
				$this->showTopPanel(false);
			}
			else
				$this->message('Нельзя дружить сам с собой', 'Предложение дружбы');
		}
		else
			$this->message('Друг не найден', 'Предложение дружбы');
	}

	public function requestsAction ($isMy = false)
	{
		if ($isMy !== false)
			$isMy = true;

		$this->view->pick('buddy/requests');
		$this->indexAction(true, $isMy);
	}

	public function deleteAction ($id)
	{
		$buddy = $this->db->query("SELECT * FROM game_buddy WHERE `id` = '" . intval($id) . "'")->fetch();

		if (isset($buddy['id']))
		{
			if ($buddy['owner'] == $this->user->id && $buddy['active'] == 1)
				$this->db->delete('game_buddy', 'id = ?', [$buddy['id']]);
			elseif ($buddy['sender'] == $this->user->id)
				$this->db->delete('game_buddy', 'id = ?', [$buddy['id']]);
			else
				$this->message('Заявка не найдена', 'Ошибка');

			$this->response->redirect('buddy/');
		}
		else
			$this->message('Заявка не найдена', 'Ошибка');
	}

	public function approveAction ($id)
	{
		$buddy = $this->db->query("SELECT * FROM game_buddy WHERE `id` = '" . intval($id) . "'")->fetch();

		if (isset($buddy['id']))
		{
			if ($buddy['owner'] == $this->user->id && $buddy['active'] == 0)
				$this->db->query("UPDATE game_buddy SET `active` = '1' WHERE `id` = '" . $buddy['id'] . "'");
			else
				$this->message('Заявка не найдена', 'Ошибка');

			$this->response->redirect('buddy/');
		}
		else
			$this->message('Заявка не найдена', 'Ошибка');
	}
	
	public function indexAction ($isRequests = false, $isMy = false)
	{
		if ($isRequests)
			$parse['title'] = $isMy ? 'Мои запросы' : 'Другие запросы';

		$parse['list'] = [];
		$parse['isMy'] = $isMy;

		$query = $isRequests ? ($isMy ? "active = 0 AND ignor = 0 AND sender = ".$this->user->id : "active = 0 AND ignor = 0 AND owner = " . $this->user->id) : "active = 1 AND ignor = 0 AND (sender = " . $this->user->id . " OR owner = " . $this->user->id . ")";
		
		$buddyrow = $this->db->query("SELECT * FROM game_buddy WHERE " . $query);

		while ($b = $buddyrow->fetch())
		{
			$q = [];

			$uid = ($b["owner"] == $this->user->id) ? $b["sender"] : $b["owner"];
		
			$u = $this->db->query("SELECT id, username, galaxy, system, planet, onlinetime, ally_id, ally_name FROM game_users WHERE id = " . $uid)->fetch();
		
			$UserAlly = ($u["ally_id"] != 0) ? "<a href=\"/alliance/info/" . $u["ally_id"] . "/\">" . $u["ally_name"] . "</a>" : "";
		
			if ($isRequests)
				$LastOnline = $b["text"];
			else
			{
				$LastOnline = "<font color=";
		
				if ($u["onlinetime"] + 60 * 10 >= time())
					$LastOnline .= "lime>В игре";
				elseif ($u["onlinetime"] + 60 * 20 >= time())
					$LastOnline .= "yellow>15 мин.";
				else
					$LastOnline .= "red>Не в игре";
		
				$LastOnline .= "</font>";
			}
		
			$q['id'] = $b["id"];
			$q['userid'] = $u["id"];
			$q['username'] = $u["username"];
			$q['ally'] = $UserAlly;
			$q['g'] = $u["galaxy"];
			$q['s'] = $u["system"];
			$q['p'] = $u["planet"];
			$q['online'] = $LastOnline;
		
			$parse['list'][] = $q;
		}

		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Список друзей');
		$this->showTopPanel(false);
	}
}

?>