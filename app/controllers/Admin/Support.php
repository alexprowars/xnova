<?php
namespace App\Controllers\Admin;

use App\Controllers\AdminController;

class Support
{
	public function show (AdminController $controller)
	{
		if ($controller->user->authlevel < 1)
			$controller->message(_getText('sys_noalloaw'), _getText('sys_noaccess'));

		$ID = (isset($_REQUEST['id'])) ? intval($_REQUEST['id']) : 0;

		if (isset($_REQUEST['mode']) && isset($_REQUEST['id']))
		{
			switch ($_REQUEST['mode'])
			{
				case 'send':

					$text = nl2br($_POST['text']);

					if (!$text)
						$controller->message('Не заполнены все поля', 'Ошибка', '?set=admin&mode=support', 3);

					$ticket = $controller->db->query("SELECT `player_id`, `text` FROM game_support WHERE `id` = '" . $ID . "';")->fetch();

					if (isset($ticket['player_id']))
					{
						$newtext = $ticket['text'].'<br><br><hr>' . $controller->user->username.'  ответил в '.date("d.m.Y H:i:s", time()).':<br>' . $text;

						$controller->db->query("UPDATE game_support SET `text` = '".addslashes($newtext)."',`status` = '2' WHERE `id` = '".$ID."'");

						$controller->game->sendMessage($ticket['player_id'], false, time(), 4, $controller->user->username, 'Поступил ответ на тикет №' . $ID);
					}

					break;

				case 'open':

					$ticket = $controller->db->query("SELECT id, text, player_id FROM game_support WHERE `id` = '" . $ID . "';")->fetch();

					if (isset($ticket['id']))
					{
						$newtext = $ticket['text'] . '<br><br><hr>' . $controller->user->username . ' открыл тикет в ' . date("j. M Y H:i:s", time());

						$controller->db->query("UPDATE game_support SET `text` = '" . addslashes($newtext) . "', `status` = '2' WHERE `id` = '" . $ID . "'");

						$controller->game->sendMessage($ticket['player_id'], false, time(), 4, $controller->user->username, 'Был открыт тикет №' . $ID);
					}

					break;

				case 'close':

					$ticket = $controller->db->query("SELECT id, text, player_id FROM game_support WHERE `id` = '" . $ID . "';")->fetch();

					if (isset($ticket['id']))
					{
						$newtext = $ticket['text'] . '<br><br><hr>' . $controller->user->username . ' закрыл тикет в ' . date("j. M Y H:i:s", time());

						$controller->db->query("UPDATE game_support SET `text` = '" . addslashes($newtext) . "', `status` = '0' WHERE `id` = '" . $ID . "'");

						$controller->game->sendMessage($ticket['player_id'], false, time(), 4, $controller->user->username, 'Тикет №'.$ID.' закрыт');
					}

					break;
			}
		}

		$tickets = array('open' => array(), 'closed' => array());

		$query = $controller->db->query("SELECT s.*, u.username FROM game_support s, game_users u WHERE u.id = s.player_id AND status != 0 ORDER BY s.time LIMIT 100;");

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

			if (isset($_GET['mode']) && $_GET['mode'] == 'detail' && $ID == $ticket['ID'])
				$TINFO = $ticket;

			if ($ticket['status'] == 0)
			{
				if (isset($_GET['mode']) && $_GET['mode'] == 'detail')
					continue;

				$tickets['closed'][] = array(
					'id' => $ticket['ID'],
					'username' => $ticket['username'],
					'subject' => $ticket['subject'],
					'status' => $status,
					'date' => date("d.m.Y H:i:s", $ticket['time'])
				);
			}
			else
			{
				$tickets['open'][] = array(
					'id' => $ticket['ID'],
					'username' => $ticket['username'],
					'subject' => $ticket['subject'],
					'status' => $status,
					'date' => date("d.m.Y H:i:s", $ticket['time'])
				);
			}
		}

		$controller->view->pick('admin/support');

		if (isset($_GET['mode']) && $_GET['mode'] == 'detail' && isset($TINFO))
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

			$parse = array(
				't_id' => $TINFO['ID'],
				't_username' => $TINFO['username'],
				't_statustext' => $status,
				't_status' => $TINFO['status'],
				't_text' => strtr($TINFO['text'], Array('\n\r' => '<br>', '\n' => '<br>')),
				't_subject' => $TINFO['subject'],
				't_date' => date("j. M Y H:i:s", $TINFO['time']),
			);

			$controller->view->setVar('parse', $parse);
		}

		$controller->view->setVar('tickets', $tickets);
		$controller->tag->setTitle('Техподдержка');
	}
}

?>