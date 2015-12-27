<?php

namespace App\Controllers;

use Xcms\db;
use Xcms\request;
use Xcms\strings;
use Xnova\User;
use Xnova\pageHelper;

class MessagesController extends ApplicationController
{
	function __construct ()
	{
		parent::__construct();

		strings::includeLang('messages');
	}
	
	public function write ()
	{
		$OwnerID = htmlspecialchars(addslashes(request::G('id', '')));
	
		if (!$OwnerID)
			$this->message(_getText('mess_no_ownerid'), _getText('mess_error'));

		$OwnerRecord = db::query("SELECT `id`, `username`, `galaxy`, `system`, `planet` FROM game_users WHERE ".(is_numeric($OwnerID) ? '`id`' : '`username`')." = '" . $OwnerID . "';", true);

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

			if (!$error && user::get()->data['message_block'] > time())
			{
				$error++;
				$msg = "<div class=error>" . _getText('mess_similar') . "</div>";
			}

			if (user::get()->data['lvl_minier'] == 1 && user::get()->data['lvl_raid'])
			{
				$registerTime = db::first(db::query("SELECT register_time FROM game_users_info WHERE id = ".user::get()->data['id']."", true));

				if ($registerTime > time() - 86400)
				{
					$lastSend = db::first(db::query("SELECT COUNT(*) as num FROM game_messages WHERE message_sender = " . user::get()->data['id'] . " AND message_time > ".(time() - (1 * 60))."", true));

					if ($lastSend > 0)
					{
						$error++;
						$msg = "<div class=error>" . _getText('mess_limit') . "</div>";
					}
				}
			}

			if (!$error)
			{
				$similar = db::query("SELECT message_text FROM game_messages WHERE message_sender = " . user::get()->data['id'] . " AND message_time > ".(time() - (5 * 60))." ORDER BY message_time DESC LIMIT 1", true);

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

				$From = user::get()->data['username'] . " [" . user::get()->data['galaxy'] . ":" . user::get()->data['system'] . ":" . user::get()->data['planet'] . "]";
				$Message = strings::FormatText($_POST['text']);
				$Message = preg_replace('/[ ]+/',' ', $Message);
				$Message = strtr($Message, _getText('stopwords'));

				user::get()->sendMessage($OwnerRecord['id'], false, 0, 1, $From, $Message);
			}
		}

		$this->setTemplate('message_new');
		$this->set('msg', $msg);
		$this->set('text', '');
		$this->set('id', $OwnerRecord['id']);
		$this->set('to', $OwnerRecord['username'] . " [" . $OwnerRecord['galaxy'] . ":" . $OwnerRecord['system'] . ":" . $OwnerRecord['planet'] . "]");

		if (isset($_GET['quote']))
		{
			$mes = db::query("SELECT message_id, message_text FROM game_messages WHERE message_id = " . intval($_GET['quote']) . " AND (message_owner = " . user::get()->data['id'] . " || message_sender = " . user::get()->data['id'] . ");", true);

			if (isset($mes['message_id']))
			{
				$this->set('text', '[quote]' . preg_replace('/\<br(\s*)?\/?\>/iu', "", $mes['message_text']) . '[/quote]', 'message');
			}
		}

		$this->setTitle('Сообщения');
		$this->showTopPanel(false);
		$this->display();
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
			db::query("UPDATE game_messages SET message_deleted = '1' WHERE `message_id` IN (" . $Mess_Array . ") AND `message_owner` = " . user::get()->data['id'] . ";");
		}

		request::redirectTo('?set=messages');	
	}
	
	public function show ()
	{
		$html = "";

		if (isset($_GET['abuse']))
		{
			$mes = db::query("SELECT * FROM game_messages WHERE message_id = " . intval($_GET['abuse']) . " AND message_owner = " . user::get()->data['id'] . ";", true);

			if (isset($mes['message_id']))
			{
				$c = db::query("SELECT `id` FROM game_users WHERE `authlevel` != 0");

				while ($cc = db::fetch_assoc($c))
				{
					user::get()->sendMessage($cc['id'], user::get()->data['id'], 0, 1, '<font color=red>' . user::get()->data['username'] . '</font>', 'От кого: ' . $mes['message_from'] . '<br>Дата отправления: ' . date("d-m-Y H:i:s", $mes['message_time']) . '<br>Текст сообщения: ' . $mes['message_text']);
				}

				$html .= "<script type='text/javascript'>alert('Жалоба отправлена администрации игры');</script>";
			}
		}

		$parse = array();

		$parse['types'] = array(0, 1, 2, 3, 4, 5, 15, 99, 100, 101);
		$parse['limit'] = array(5, 10, 25, 50, 100, 200);

		$MessCategory = (!isset($_POST['messcat'])) ? (isset($_SESSION['m_cat']) ? $_SESSION['m_cat'] : 100) : intval($_POST['messcat']);
		$lim = (!isset($_POST['show_by']) || !in_array(intval($_POST['show_by']), $parse['limit'])) ? (isset($_SESSION['m_limit']) ? $_SESSION['m_limit'] : 10) : intval($_POST['show_by']);
		$start = request::R('p', 0, VALUE_INT);

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

		if (user::get()->data['new_message'] > 0)
		{
			db::query("UPDATE game_users SET `new_message` = 0 WHERE `id` = " . user::get()->data['id'] . "");
			user::get()->data['new_message'] = 0;
		}

		if ($MessCategory < 100)
			$totalCount = db::first(db::query("SELECT COUNT(message_id) as kol FROM game_messages WHERE `message_owner` = '" . user::get()->data['id'] . "' AND message_type = " . $MessCategory . " AND message_deleted = '0'", true));
		elseif ($MessCategory == 101)
			$totalCount = db::first(db::query("SELECT COUNT(message_id) as kol FROM game_messages WHERE `message_sender` = '" . user::get()->data['id'] . "'", true));
		else
			$totalCount = db::first(db::query("SELECT COUNT(message_id) as kol FROM game_messages WHERE `message_owner` = '" . user::get()->data['id'] . "' AND message_deleted = '0'", true));

		if (!$start)
			$start = 1;

		$parse['pages'] = strings::pagination($totalCount, $lim, '?set=messages', $start);

		$limits = (($start - 1) * $lim) . "," . $lim . "";

		if ($MessCategory < 100)
			$messages = db::query("SELECT * FROM game_messages WHERE `message_owner` = '" . user::get()->data['id'] . "' AND message_type = " . $MessCategory . " AND message_deleted = '0' ORDER BY `message_time` DESC LIMIT " . $limits . ";");
		elseif ($MessCategory == 101)
			$messages = db::query("SELECT m.*, CONCAT(u.username, ' [', u.galaxy,':', u.system,':',u.planet, ']') AS message_from, m.message_owner AS message_sender FROM game_messages m LEFT JOIN game_users u ON u.id = m.message_owner WHERE m.`message_sender` = '" . user::get()->data['id'] . "' ORDER BY m.`message_time` DESC LIMIT " . $limits . ";");
		else
			$messages = db::query("SELECT * FROM game_messages WHERE `message_owner` = '" . user::get()->data['id'] . "' AND message_deleted = '0' ORDER BY `message_time` DESC LIMIT " . $limits . ";");

		$parse['list'] = db::extractResult($messages);

		$this->setTemplate('messages');
		$this->set('parse', $parse);

		$this->setTitle('Сообщения');
		$this->setContent($html);
		$this->showTopPanel(false);
		$this->display();
	}
}

?>