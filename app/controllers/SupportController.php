<?php

namespace App\Controllers;

use Xcms\db;
use Xcms\socials;
use Xcms\strings;
use Xnova\User;
use Xnova\pageHelper;

class SupportController extends ApplicationController
{
	function __construct ()
	{
		parent::__construct();
	}
	
	public function show ()
	{
		$action = (isset($_GET['action'])) ? $_GET['action'] : '';
		
		switch ($action)
		{
			case 'newticket':
		
				if (empty($_POST['text']) || empty($_POST['subject']))
					$this->message('Не заполнены все поля', 'Ошибка', '?set=support', 3);
		
				db::query("INSERT game_support SET `player_id` = '" . user::get()->data['id'] . "', `subject` = '" . strings::CheckString($_POST['subject']) . "', `text` = '" . strings::CheckString($_POST['text']) . "', `time` = " . time() . ", `status` = '1';");
		
				$ID = db::insert_id();
		
				$token = socials::smsGetToken();
				socials::smsSend(SMS_LOGIN, 'Создан новый тикет №' . $ID . ' ('.user::get()->data['username'].')', $token);
		
				$this->message('Задача добавлена', 'Успех', '?set=support', 3);
		
				break;
		
			case 'send':
		
				if (isset($_GET['id']))
				{
					$TicketID = intval($_GET['id']);
		
					if (empty($_POST['text']))
						$this->message('Не заполнены все поля', 'Ошибка', '?set=support', 3);
		
					$ticket = db::query("SELECT id, text, status FROM game_support WHERE `id` = '" . $TicketID . "';", true);
		
					if (isset($ticket['id']))
					{
						$text = $ticket['text'] . '<hr>' . user::get()->data['username'] . ' ответил в ' . date("d.m.Y H:i:s", time()) . ':<br>' . strings::CheckString($_POST['text']) . '';
		
						db::query("UPDATE game_support SET `text` = '" . addslashes($text) . "',`status` = '3' WHERE `id` = '" . $TicketID . "';");
		
						user::get()->sendMessage(1, false, time(), 4, user::get()->data['username'], 'Поступил ответ на тикет №' . $TicketID);
		
						$this->message('Задача обновлена', 'Успех', '?set=support', 3);
		
						if ($ticket['status'] == 2)
						{
							$token = socials::smsGetToken();
							socials::smsSend(SMS_LOGIN, 'Поступил ответ на тикет №' . $ticket['id'] . ' ('.user::get()->data['username'].')', $token);
						}
					}
				}
		
				break;
		
			default:
		
				$parse = array();
		
				$supports = db::query("SELECT ID, time, text, subject, status FROM game_support WHERE (`player_id` = '" . user::get()->data['id'] . "') ORDER BY time DESC;");
		
				$parse['TicketsList'] = array();
		
				while ($ticket = db::fetch_assoc($supports))
				{
					$parse['TicketsList'][$ticket['ID']] = array
					(
						'status' => $ticket['status'],
						'subject' => $ticket['subject'],
						'date' => datezone("d.m.Y H:i:s", $ticket['time']),
						'text' => html_entity_decode($ticket['text'], ENT_NOQUOTES, "CP1251"),
					);
				}
		
				$this->setTemplate('support');
				$this->set('parse', $parse);

				$this->setTitle('Техподдержка');
				$this->showTopPanel(false);
				$this->display();
		}
	}
}

?>