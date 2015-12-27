<?php

namespace App\Controllers;

use Xcms\db;
use Xcms\sql;
use Xnova\User;
use Xnova\pageHelper;

class BuddyController extends ApplicationController
{
	function __construct ()
	{
		parent::__construct();
	}
	
	public function show ()
	{
		$a = @$_GET['a'];
		$e = @$_GET['e'];
		$s = @$_GET['s'];
		$u = @intval($_GET['u']);
		
		if ($s == 1 && isset($_GET['bid']))
		{
			$bid = intval($_GET['bid']);
		
			$buddy = db::query("SELECT * FROM game_buddy WHERE `id` = '" . $bid . "';", true);
		
			if ($buddy['owner'] == user::get()->data['id'])
			{
				if ($buddy['active'] == 0 && $a == 1)
					db::query("DELETE FROM game_buddy WHERE `id` = '" . $bid . "';");
				elseif ($buddy['active'] == 1)
					db::query("DELETE FROM game_buddy WHERE `id` = '" . $bid . "';");
				elseif ($buddy['active'] == 0)
					db::query("UPDATE game_buddy SET `active` = '1' WHERE `id` = '" . $bid . "';");
			}
			elseif ($buddy['sender'] == user::get()->data['id'])
				db::query("DELETE FROM game_buddy WHERE `id` = '" . $bid . "';");
		}
		elseif (isset($_POST["s"]) && $_POST["s"] == 3 && $_POST["a"] == 1 && $_POST["e"] == 1 && isset($_POST["u"]))
		{
			$u = intval($_POST["u"]);
		
			$buddy = db::query("SELECT * FROM game_buddy WHERE sender = ".user::get()->data["id"]." AND owner={$u} OR sender={$u} AND owner = ".user::get()->data["id"]."", true);
		
			if (!$buddy)
			{
				if (mb_strlen($_POST['text'], 'UTF-8') > 5000)
					$this->message("Максимальная длинна сообщения 5000 семволов!", "Ошибка");
		
				sql::build()->insert('game_buddy')->set(Array
				(
					'sender'	=> user::get()->data["id"],
					'owner'		=> $u,
					'active'	=> 0,
					'text'		=> db::escape_string(strip_tags($_POST['text']))
				))->execute();
		
				user::get()->sendMessage($u, 0, time(), 1, 'Запрос дружбы', 'Игрок '.user::get()->data["username"].' отправил вам запрос на добавление в друзья. <a href="?set=buddy&a=1"><< просмотреть >></a>');
		
				$this->message('Запрос отправлен', 'Предложение дружбы', '?set=buddy');
			}
			else
				$this->message('Запрос дружбы был уже отправлен ранее', 'Предложение дружбы');
		}
		
		if ($a == 2 && isset($u))
		{
			$u = db::query("SELECT id, username FROM game_users WHERE id = '" . $u . "'", true);
		
			if (isset($u) && $u['id'] != user::get()->data['id'])
			{
				$parse['id'] = $u['id'];
				$parse['username'] = $u['username'];
		
				$this->setTemplate('buddy_new');
				$this->set('parse', $parse);

				$this->setTitle('Друзья');
				$this->showTopPanel(false);
				$this->display();
			}
			elseif ($u['id'] == user::get()->data['id'])
				$this->message('Нельзя дружить сам с собой', 'Предложение дружбы');
		}
		
		$TableTitle = ($a == 1) ? (($e == 1) ? 'Мои запросы' : 'Другие запросы') : 'Список друзей';
		
		$parse['title'] = $TableTitle;
		$parse['a'] = (!isset($a)) ? false : true;
		$parse['list'] = array();
		
		$query = ($a == 1) ? (($e == 1) ? "WHERE active=0 AND ignor=0 AND sender=" . user::get()->data["id"] : "WHERE active=0 AND ignor=0 AND owner=" . user::get()->data["id"]) : "WHERE active = 1 AND ignor=0 AND (sender = " . user::get()->data["id"] . " OR owner = " . user::get()->data["id"] . ")";
		
		$buddyrow = db::query("SELECT * FROM game_buddy " . $query);
		
		$i = 0;
		
		while ($b = db::fetch_assoc($buddyrow))
		{
			$q = array();
		
			$i++;
			$uid = ($b["owner"] == user::get()->data["id"]) ? $b["sender"] : $b["owner"];
		
			$u = db::query("SELECT id, username, galaxy, system, planet, onlinetime, ally_id, ally_name FROM game_users WHERE id=" . $uid, true);
		
			$UserAlly = ($u["ally_id"] != 0) ? "<a href=?set=alliance&mode=ainfo&a=" . $u["ally_id"] . ">" . $u["ally_name"] . "</a>" : "";
		
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
				$UserCommand = "<a href=?set=buddy&s=1&bid=" . $b["id"] . ">Удалить запрос</a>";
			elseif (isset($a))
			{
				$UserCommand = "<a href=?set=buddy&s=1&bid=" . $b["id"] . ">Применить</a><br/>";
				$UserCommand .= "<a href=?set=buddy&a=1&s=1&bid=" . $b["id"] . ">Отклонить</a></a>";
			}
			else
				$UserCommand = "<a href=?set=buddy&s=1&bid=" . $b["id"] . ">Удалить</a>";
		
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
		
		$this->setTemplate('buddy_list');
		$this->set('parse', $parse);

		$this->setTitle('Список друзей');
		$this->showTopPanel(false);
		$this->display();
	}
}

?>