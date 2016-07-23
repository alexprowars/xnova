<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use App\Helpers;
use App\Models\User;
use App\Sms;
use Xnova\Controller;

class SupportController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
	}

	public function newAction ()
	{
		if (empty($_POST['text']) || empty($_POST['subject']))
			$this->message('Не заполнены все поля', 'Ошибка', '/support/', 3);

		$this->db->query("INSERT game_support SET `player_id` = '" . $this->user->id . "', `subject` = '" . Helpers::CheckString($_POST['subject']) . "', `text` = '" . Helpers::CheckString($_POST['text']) . "', `time` = " . time() . ", `status` = '1';");

		$ID = $this->db->lastInsertId();

		$sms = new Sms();
		$sms->send($this->config->sms->login, 'Создан новый тикет №' . $ID . ' ('.$this->user->username.')');

		$this->message('Задача добавлена', 'Успех', '/support/', 3);
	}

	public function answerAction ($id)
	{
		if ($id > 0)
		{
			$TicketID = intval($id);

			if (empty($_POST['text']))
				$this->message('Не заполнены все поля', 'Ошибка', '/support/', 3);

			$ticket = $this->db->query("SELECT id, text, status FROM game_support WHERE id = '" . $TicketID . "';")->fetch();

			if (isset($ticket['id']))
			{
				$text = $ticket['text'] . '<hr>' . $this->user->username . ' ответил в ' . date("d.m.Y H:i:s", time()) . ':<br>' . Helpers::CheckString($_POST['text']) . '';

				$this->db->query("UPDATE game_support SET text = '" . addslashes($text) . "', status = '3' WHERE id = '" . $TicketID . "';");

				User::sendMessage(1, false, time(), 4, $this->user->username, 'Поступил ответ на тикет №' . $TicketID);

				$this->message('Задача обновлена', 'Успех', '/support/', 3);

				if ($ticket['status'] == 2)
				{
					$sms = new Sms();
					$sms->send($this->config->sms->login, 'Поступил ответ на тикет №' . $ticket['id'] . ' ('.$this->user->username.')');
				}
			}
		}
	}
	
	public function indexAction ()
	{
		$list = [];

		$supports = $this->db->query("SELECT ID, time, text, subject, status FROM game_support WHERE (player_id = '" . $this->user->id . "') ORDER BY time DESC;");

		while ($ticket = $supports->fetch())
		{
			$list[$ticket['ID']] = [
				'status' => $ticket['status'],
				'subject' => $ticket['subject'],
				'date' => $this->game->datezone("d.m.Y H:i:s", $ticket['time']),
				'text' => html_entity_decode($ticket['text'], ENT_NOQUOTES, "CP1251"),
			];
		}

		$this->view->setVar('list', $list);

		$this->tag->setTitle('Техподдержка');
		$this->showTopPanel(false);
	}
}