<?php

namespace App\Controllers;

use App\Helpers;
use App\Lang;

class MessagesController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();

		Lang::includeLang('messages');
	}
	
	public function write ()
	{
		$OwnerID = htmlspecialchars(addslashes($this->request->getQuery('id')));
	
		if (!$OwnerID)
			$this->message(_getText('mess_no_ownerid'), _getText('mess_error'));

		$OwnerRecord = $this->db->query("SELECT `id`, `username`, `galaxy`, `system`, `planet` FROM game_users WHERE ".(is_numeric($OwnerID) ? '`id`' : '`username`')." = '" . $OwnerID . "';")->fetch();

		if (!isset($OwnerRecord['id']))
			$this->message(_getText('mess_no_owner'), _getText('mess_error'));

		$msg = '';

		if (isset($_POST['text']))
		{
			$error = 0;

			if (!$_POST["text"])
			{
				$error++;
				$msg = "<div class=error>" . _getText('mess_no_text') . "</div>";
			}

			if (!$error && $this->user->message_block > time())
			{
				$error++;
				$msg = "<div class=error>" . _getText('mess_similar') . "</div>";
			}

			if ($this->user->lvl_minier == 1 && $this->user->lvl_raid)
			{
				$registerTime = $this->db->fetchColumn("SELECT register_time FROM game_users_info WHERE id = ".$this->user->id."");

				if ($registerTime > time() - 86400)
				{
					$lastSend = $this->db->fetchColumn("SELECT COUNT(*) as num FROM game_messages WHERE message_sender = " . $this->user->id . " AND message_time > ".(time() - (1 * 60))."");

					if ($lastSend > 0)
					{
						$error++;
						$msg = "<div class=error>" . _getText('mess_limit') . "</div>";
					}
				}
			}

			if (!$error)
			{
				$similar = $this->db->query("SELECT message_text FROM game_messages WHERE message_sender = " . $this->user->id . " AND message_time > ".(time() - (5 * 60))." ORDER BY message_time DESC LIMIT 1")->fetch();

				if (isset($similar['message_text']))
				{
					if (mb_strlen($similar['message_text'], 'UTF-8') < 1000)
					{
						similar_text($_POST["text"], $similar['message_text'], $sim);

						if ($sim > 80)
						{
							$error++;
							$msg = "<div class=error>" . _getText('mess_similar') . "</div>";
						}
					}
				}
			}

			if ($error == 0)
			{
				$msg = "<div class=success>" . _getText('mess_sended') . "</div>";

				$From = $this->user->username . " [" . $this->user->galaxy . ":" . $this->user->system . ":" . $this->user->planet . "]";
				$Message = Helpers::FormatText($_POST['text']);
				$Message = preg_replace('/[ ]+/',' ', $Message);
				$Message = strtr($Message, _getText('stopwords'));

				$this->game->sendMessage($OwnerRecord['id'], false, 0, 1, $From, $Message);
			}
		}

		$this->view->pick('message_new');
		$this->view->setVar('msg', $msg);
		$this->view->setVar('text', '');
		$this->view->setVar('id', $OwnerRecord['id']);
		$this->view->setVar('to', $OwnerRecord['username'] . " [" . $OwnerRecord['galaxy'] . ":" . $OwnerRecord['system'] . ":" . $OwnerRecord['planet'] . "]");

		if (isset($_GET['quote']))
		{
			$mes = $this->db->query("SELECT message_id, message_text FROM game_messages WHERE message_id = " . intval($_GET['quote']) . " AND (message_owner = " . $this->user->id . " || message_sender = " . $this->user->id . ");", true);

			if (isset($mes['message_id']))
			{
				$this->view->setVar('text', '[quote]' . preg_replace('/\<br(\s*)?\/?\>/iu', "", $mes['message_text']) . '[/quote]');
			}
		}

		$this->tag->setTitle('Сообщения');
		$this->showTopPanel(false);
	}
	
	public function delete ()
	{
		$Mess_Array = array();

		foreach ($_POST as $Message => $Answer)
		{
			if (preg_match("/delmes/iu", $Message) && $Answer == 'on')
			{
				$Mess_Array[] = str_replace("delmes", "", $Message);
			}
		}

		$Mess_Array = implode(',', $Mess_Array);

		if ($Mess_Array != '')
		{
			$this->db->query("UPDATE game_messages SET message_deleted = '1' WHERE `message_id` IN (" . $Mess_Array . ") AND `message_owner` = " . $this->user->id . ";");
		}

		$this->response->redirect('?set=messages');
	}
	
	public function indexAction ()
	{
		$html = "";

		if (isset($_GET['abuse']))
		{
			$mes = $this->db->query("SELECT * FROM game_messages WHERE message_id = " . intval($_GET['abuse']) . " AND message_owner = " . $this->user->id . ";")->fetch();

			if (isset($mes['message_id']))
			{
				$c = $this->db->query("SELECT `id` FROM game_users WHERE `authlevel` != 0");

				while ($cc = $c->fetch())
				{
					$this->game->sendMessage($cc['id'], $this->user->id, 0, 1, '<font color=red>' . $this->user->username . '</font>', 'От кого: ' . $mes['message_from'] . '<br>Дата отправления: ' . date("d-m-Y H:i:s", $mes['message_time']) . '<br>Текст сообщения: ' . $mes['message_text']);
				}

				$html .= "<script type='text/javascript'>alert('Жалоба отправлена администрации игры');</script>";
			}
		}

		$parse = array();

		$parse['types'] = array(0, 1, 2, 3, 4, 5, 15, 99, 100, 101);
		$parse['limit'] = array(5, 10, 25, 50, 100, 200);

		$MessCategory = (!isset($_POST['messcat'])) ? (isset($_SESSION['m_cat']) ? $_SESSION['m_cat'] : 100) : intval($_POST['messcat']);
		$lim = (!isset($_POST['show_by']) || !in_array(intval($_POST['show_by']), $parse['limit'])) ? (isset($_SESSION['m_limit']) ? $_SESSION['m_limit'] : 10) : intval($_POST['show_by']);
		$start = $this->request->get('p', 'int', 0);

		if (!isset($_SESSION['m_limit']) || $_SESSION['m_limit'] != $lim)
			$_SESSION['m_limit'] = $lim;

		if (!isset($_SESSION['m_cat']) || $_SESSION['m_cat'] != $MessCategory)
			$_SESSION['m_cat'] = $MessCategory;

		if (isset($_POST['deletemessages']))
		{
			$this->delete();
		}

		$parse['lim'] = $lim;
		$parse['category'] = $MessCategory;

		if ($this->user->messages > 0)
		{
			$this->db->query("UPDATE game_users SET `messages` = 0 WHERE `id` = " . $this->user->id . "");
			$this->user->messages = 0;
		}

		if ($MessCategory < 100)
			$totalCount = $this->db->fetchColumn("SELECT COUNT(message_id) as kol FROM game_messages WHERE `message_owner` = '" . $this->user->id . "' AND message_type = " . $MessCategory . " AND message_deleted = '0'");
		elseif ($MessCategory == 101)
			$totalCount = $this->db->fetchColumn("SELECT COUNT(message_id) as kol FROM game_messages WHERE `message_sender` = '" . $this->user->id . "'");
		else
			$totalCount = $this->db->fetchColumn("SELECT COUNT(message_id) as kol FROM game_messages WHERE `message_owner` = '" . $this->user->id . "' AND message_deleted = '0'");

		if (!$start)
			$start = 1;

		$parse['pages'] = Helpers::pagination($totalCount, $lim, '?set=messages', $start);

		$limits = (($start - 1) * $lim) . "," . $lim . "";

		if ($MessCategory < 100)
			$messages = $this->db->query("SELECT * FROM game_messages WHERE `message_owner` = '" . $this->user->id . "' AND message_type = " . $MessCategory . " AND message_deleted = '0' ORDER BY `message_time` DESC LIMIT " . $limits . ";");
		elseif ($MessCategory == 101)
			$messages = $this->db->query("SELECT m.*, CONCAT(u.username, ' [', u.galaxy,':', u.system,':',u.planet, ']') AS message_from, m.message_owner AS message_sender FROM game_messages m LEFT JOIN game_users u ON u.id = m.message_owner WHERE m.`message_sender` = '" . $this->user->id . "' ORDER BY m.`message_time` DESC LIMIT " . $limits . ";");
		else
			$messages = $this->db->query("SELECT * FROM game_messages WHERE `message_owner` = '" . $this->user->id . "' AND message_deleted = '0' ORDER BY `message_time` DESC LIMIT " . $limits . ";");

		$parse['list'] = $this->db->extractResult($messages);

		$this->view->pick('messages');
		$this->view->setVar('parse', $parse);

		$this->tag->setTitle('Сообщения');
		$this->view->setVar('html', $html);
		$this->showTopPanel(false);
	}
}

?>