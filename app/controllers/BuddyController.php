<?php

namespace App\Controllers;

use App\Sql;

class BuddyController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();
	}
	
	public function indexAction ()
	{
		$a = @$_GET['a'];
		$e = @$_GET['e'];
		$s = @$_GET['s'];
		$u = @intval($_GET['u']);
		
		if ($s == 1 && isset($_GET['bid']))
		{
			$bid = intval($_GET['bid']);
		
			$buddy = $this->db->query("SELECT * FROM game_buddy WHERE `id` = '" . $bid . "';")->fetch();
		
			if ($buddy['owner'] == $this->user->id)
			{
				if ($buddy['active'] == 0 && $a == 1)
					$this->db->query("DELETE FROM game_buddy WHERE `id` = '" . $bid . "';");
				elseif ($buddy['active'] == 1)
					$this->db->query("DELETE FROM game_buddy WHERE `id` = '" . $bid . "';");
				elseif ($buddy['active'] == 0)
					$this->db->query("UPDATE game_buddy SET `active` = '1' WHERE `id` = '" . $bid . "';");
			}
			elseif ($buddy['sender'] == $this->user->id)
				$this->db->query("DELETE FROM game_buddy WHERE `id` = '" . $bid . "';");
		}
		elseif (isset($_POST["s"]) && $_POST["s"] == 3 && $_POST["a"] == 1 && $_POST["e"] == 1 && isset($_POST["u"]))
		{
			$u = intval($_POST["u"]);
		
			$buddy = $this->db->query("SELECT * FROM game_buddy WHERE sender = ".$this->user->id." AND owner={$u} OR sender={$u} AND owner = ".$this->user->id."")->fetch();
		
			if (!$buddy)
			{
				if (mb_strlen($_POST['text'], 'UTF-8') > 5000)
					$this->message("Максимальная длинна сообщения 5000 семволов!", "Ошибка");
		
				$this->db->insertAsDict('game_buddy',
				[
					'sender'	=> $this->user->id,
					'owner'		=> $u,
					'active'	=> 0,
					'text'		=> strip_tags($_POST['text'])
				]);
		
				$this->game->sendMessage($u, 0, time(), 1, 'Запрос дружбы', 'Игрок '.$this->user->username.' отправил вам запрос на добавление в друзья. <a href="/buddy/?a=1"><< просмотреть >></a>');
		
				$this->message('Запрос отправлен', 'Предложение дружбы', '/buddy/');
			}
			else
				$this->message('Запрос дружбы был уже отправлен ранее', 'Предложение дружбы');
		}
		
		if ($a == 2 && isset($u))
		{
			$u = $this->db->query("SELECT id, username FROM game_users WHERE id = '" . $u . "'")->fetch();
		
			if (isset($u) && $u['id'] != $this->user->id)
			{
				$parse['id'] = $u['id'];
				$parse['username'] = $u['username'];
		
				$this->view->pick('buddy_new');
				$this->view->setVar('parse', $parse);

				$this->tag->setTitle('Друзья');
				$this->showTopPanel(false);
			}
			elseif ($u['id'] == $this->user->id)
				$this->message('Нельзя дружить сам с собой', 'Предложение дружбы');
		}
		
		$TableTitle = ($a == 1) ? (($e == 1) ? 'Мои запросы' : 'Другие запросы') : 'Список друзей';
		
		$parse['title'] = $TableTitle;
		$parse['a'] = (!isset($a)) ? false : true;
		$parse['list'] = array();
		
		$query = ($a == 1) ? (($e == 1) ? "WHERE active=0 AND ignor=0 AND sender=" . $this->user->id : "WHERE active=0 AND ignor=0 AND owner=" . $this->user->id) : "WHERE active = 1 AND ignor=0 AND (sender = " . $this->user->id . " OR owner = " . $this->user->id . ")";
		
		$buddyrow = $this->db->query("SELECT * FROM game_buddy " . $query);
		
		$i = 0;
		
		while ($b = $buddyrow->fetch())
		{
			$q = array();
		
			$i++;
			$uid = ($b["owner"] == $this->user->id) ? $b["sender"] : $b["owner"];
		
			$u = $this->db->query("SELECT id, username, galaxy, system, planet, onlinetime, ally_id, ally_name FROM game_users WHERE id=" . $uid)->fetch();
		
			$UserAlly = ($u["ally_id"] != 0) ? "<a href=/alliance/?mode=ainfo&a=" . $u["ally_id"] . ">" . $u["ally_name"] . "</a>" : "";
		
			if (isset($a))
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
		
			if (isset($a) && isset($e))
				$UserCommand = "<a href=/buddy/?s=1&bid=" . $b["id"] . ">Удалить запрос</a>";
			elseif (isset($a))
			{
				$UserCommand = "<a href=/buddy/?s=1&bid=" . $b["id"] . ">Применить</a><br/>";
				$UserCommand .= "<a href=/buddy/?a=1&s=1&bid=" . $b["id"] . ">Отклонить</a></a>";
			}
			else
				$UserCommand = "<a href=/buddy/?s=1&bid=" . $b["id"] . ">Удалить</a>";
		
			$q['id'] = $u["id"];
			$q['username'] = $u["username"];
			$q['ally'] = $UserAlly;
			$q['g'] = $u["galaxy"];
			$q['s'] = $u["system"];
			$q['p'] = $u["planet"];
			$q['online'] = $LastOnline;
			$q['c'] = $UserCommand;
		
			$parse['list'][] = $q;
		}
		
		$this->view->pick('buddy_list');
		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Список друзей');
		$this->showTopPanel(false);
	}
}

?>