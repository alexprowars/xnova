<?php

namespace Admin\Controllers;

use Admin\Controller;
use Xnova\User;

/**
 * @RoutePrefix("/admin/support")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class SupportController extends Controller
{
	const CODE = 'support';

	public function initialize ()
	{
		parent::initialize();

		if (!$this->access->canReadController(self::CODE, 'admin'))
			throw new \Exception('Access denied');
	}

	public static function getMenu ()
	{
		return [[
			'code'	=> 'support',
			'title' => 'Техподдержка',
			'icon'	=> 'support',
			'sort'	=> 20
		]];
	}

	public function indexAction ($id = 0)
	{
		$tickets = ['open' => [], 'closed' => []];

		$query = $this->db->query("SELECT s.*, u.username FROM game_support s, game_users u WHERE u.id = s.player_id AND status != 0 ORDER BY s.time LIMIT 100;");

		while ($ticket = $query->fetch())
		{
			switch ($ticket['status'])
			{
				case 0:
					$status = '<font color="red">закрыто</font>';
					break;
				case 1:
					$status = '<font color="green">открыто</font>';
					break;
				case 2:
					$status = '<font color="orange">ответ админа</font>';
					break;
				case 3:
					$status = '<font color="green">ответ игрока</font>';
					break;
				default:
					$status = '';
			}

			if ($id > 0 && $id == $ticket['ID'])
				$TINFO = $ticket;

			if ($ticket['status'] == 0)
			{
				if (isset($_GET['mode']) && $_GET['mode'] == 'detail')
					continue;

				$tickets['closed'][] = [
					'id' => $ticket['ID'],
					'username' => $ticket['username'],
					'subject' => $ticket['subject'],
					'status' => $status,
					'date' => date("d.m.Y H:i:s", $ticket['time'])
				];
			}
			else
			{
				$tickets['open'][] = [
					'id' => $ticket['ID'],
					'username' => $ticket['username'],
					'subject' => $ticket['subject'],
					'status' => $status,
					'date' => date("d.m.Y H:i:s", $ticket['time'])
				];
			}
		}

		if (isset($TINFO))
		{
			switch ($TINFO['status'])
			{
				case 0:
					$status = '<font color="red">закрыто</font>';
					break;
				case 1:
					$status = '<font color="green">открыто</font>';
					break;
				case 2:
					$status = '<font color="orange">ответ админа</font>';
					break;
				case 3:
					$status = '<font color="green">ответ игрока</font>';
					break;
				default:
					$status = '';
			}

			$parse = [
				't_id' => $TINFO['ID'],
				't_username' => $TINFO['username'],
				't_statustext' => $status,
				't_status' => $TINFO['status'],
				't_text' => strtr($TINFO['text'], Array('\n\r' => '<br>', '\n' => '<br>')),
				't_subject' => $TINFO['subject'],
				't_date' => date("j. M Y H:i:s", $TINFO['time']),
			];

			$this->view->setVar('parse', $parse);
		}

		$this->view->setVar('tickets', $tickets);
		$this->tag->setTitle('Техподдержка');
	}

	public function detailAction ($id)
	{
		$this->indexAction($id);
	}

	public function sendAction ($id)
	{
		if (!$this->access->canWriteController(self::CODE, 'admin'))
			throw new \Exception('Access denied');

		$text = nl2br($this->request->getPost('text'));

		if (!$text || !$id)
			$this->message('Не заполнены все поля', 'Ошибка', '/admin/support/', 3);

		$ticket = $this->db->query("SELECT player_id, text FROM game_support WHERE id = '" . $id . "'")->fetch();

		if (isset($ticket['player_id']))
		{
			$newtext = $ticket['text'].'<br><br><hr>' . $this->user->username.'  ответил в '.date("d.m.Y H:i:s", time()).':<br>' . $text;

			$this->db->query("UPDATE game_support SET text = '".addslashes($newtext)."',status = '2' WHERE id = '".$id."'");

			User::sendMessage($ticket['player_id'], false, time(), 4, $this->user->username, 'Поступил ответ на тикет №' . $id);
		}

		$this->indexAction();
	}

	public function openAction ($id)
	{
		if (!$this->access->canWriteController(self::CODE, 'admin'))
			throw new \Exception('Access denied');

		if (!$id)
			$this->message('Не заполнены все поля', 'Ошибка', '/admin/support/', 3);

		$ticket = $this->db->query("SELECT id, text, player_id FROM game_support WHERE id = '" . $id . "';")->fetch();

		if (isset($ticket['id']))
		{
			$newtext = $ticket['text'] . '<br><br><hr>' . $this->user->username . ' открыл тикет в ' . date("j. M Y H:i:s", time());

			$this->db->query("UPDATE game_support SET text = '" . addslashes($newtext) . "', status = '2' WHERE id = '" . $id . "'");

			User::sendMessage($ticket['player_id'], false, time(), 4, $this->user->username, 'Был открыт тикет №' . $id);
		}

		$this->indexAction();
	}

	public function closeAction ($id)
	{
		if (!$this->access->canWriteController(self::CODE, 'admin'))
			throw new \Exception('Access denied');

		if (!$id)
			$this->message('Не заполнены все поля', 'Ошибка', '/admin/support/', 3);

		$ticket = $this->db->query("SELECT id, text, player_id FROM game_support WHERE id = '" . $id . "';")->fetch();

		if (isset($ticket['id']))
		{
			$newtext = $ticket['text'] . '<br><br><hr>' . $this->user->username . ' закрыл тикет в ' . date("j. M Y H:i:s", time());

			$this->db->query("UPDATE game_support SET text = '" . addslashes($newtext) . "', status = '0' WHERE id = '" . $id . "'");

			User::sendMessage($ticket['player_id'], false, time(), 4, $this->user->username, 'Тикет №'.$id.' закрыт');
		}

		$this->indexAction();
	}
}