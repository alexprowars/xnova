<?php

namespace App\Http\Controllers\Admin;

use App\Engine\Enums\MessageType;
use App\Notifications\MessageNotification;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;
use App\Models\User;

class SupportController extends Controller
{
	public static function getMenu()
	{
		return [[
			'code'	=> 'support',
			'title' => 'Техподдержка',
			'icon'	=> 'la la-headset',
			'sort'	=> 20
		]];
	}

	public function index($id = 0)
	{
		$tickets = ['open' => [], 'closed' => []];

		$query = $this->db->query("SELECT s.*, u.username FROM support s, users u WHERE u.id = s.user_id AND status != 0 ORDER BY s.time LIMIT 100;");

		while ($ticket = $query->fetch()) {
			$status = match ($ticket['status']) {
				0 => '<span style="color: red">закрыто</span>',
				1 => '<span style="color: green">открыто</span>',
				2 => '<span style="color: orange">ответ админа</span>',
				3 => '<span style="color: green">ответ игрока</span>',
				default => '',
			};

			if ($id > 0 && $id == $ticket['id']) {
				$TINFO = $ticket;
			}

			if ($ticket['status'] == 0) {
				if (isset($_GET['mode']) && $_GET['mode'] == 'detail') {
					continue;
				}

				$tickets['closed'][] = [
					'id' => $ticket['id'],
					'username' => $ticket['username'],
					'subject' => $ticket['subject'],
					'status' => $status,
					'date' => date("d.m.Y H:i:s", $ticket['time'])
				];
			} else {
				$tickets['open'][] = [
					'id' => $ticket['id'],
					'username' => $ticket['username'],
					'subject' => $ticket['subject'],
					'status' => $status,
					'date' => date("d.m.Y H:i:s", $ticket['time'])
				];
			}
		}

		if (isset($TINFO)) {
			$status = match ($TINFO['status']) {
				0 => '<span style="color: red">закрыто</span>',
				1 => '<span style="color: green">открыто</span>',
				2 => '<span style="color: orange">ответ админа</span>',
				3 => '<span style="color: green">ответ игрока</span>',
				default => '',
			};

			$parse = [
				't_id' => $TINFO['id'],
				't_username' => $TINFO['username'],
				't_statustext' => $status,
				't_status' => $TINFO['status'],
				't_text' => strtr($TINFO['text'], array('\n\r' => '<br>', '\n' => '<br>')),
				't_subject' => $TINFO['subject'],
				't_date' => date("j. M Y H:i:s", $TINFO['time']),
			];

			$this->view->setVar('parse', $parse);
		}

		View::share('title', 'Техподдержка');

		return view('admin.support', ['tickets' => $tickets]);
	}

	public function detail($id)
	{
		return $this->index($id);
	}

	public function send($id)
	{
		if (!$this->access->canWriteController(self::CODE, 'admin')) {
			throw new \Exception('Access denied');
		}

		$text = nl2br($this->request->getPost('text'));

		if (!$text || !$id) {
			$this->message('Не заполнены все поля', 'Ошибка', '/admin/support/', 3);
		}

		$ticket = $this->db->query("SELECT user_id, text FROM support WHERE id = '" . $id . "'")->fetch();

		if (isset($ticket['user_id'])) {
			$newtext = $ticket['text'] . '<br><br><hr>' . $this->user->username . '  ответил в ' . date("d.m.Y H:i:s", time()) . ':<br>' . $text;

			$this->db->query("UPDATE support SET text = '" . addslashes($newtext) . "',status = '2' WHERE id = '" . $id . "'");

			User::find($ticket['user_id'])?->notify(new MessageNotification(null, MessageType::System, $this->user->username, 'Поступил ответ на тикет №' . $id));
		}

		return $this->index();
	}

	public function open($id)
	{
		if (!$this->access->canWriteController(self::CODE, 'admin')) {
			throw new \Exception('Access denied');
		}

		if (!$id) {
			$this->message('Не заполнены все поля', 'Ошибка', '/admin/support/', 3);
		}

		$ticket = $this->db->query("SELECT id, text, user_id FROM support WHERE id = '" . $id . "';")->fetch();

		if (isset($ticket['id'])) {
			$newtext = $ticket['text'] . '<br><br><hr>' . $this->user->username . ' открыл тикет в ' . date("j. M Y H:i:s", time());

			$this->db->query("UPDATE support SET text = '" . addslashes($newtext) . "', status = '2' WHERE id = '" . $id . "'");

			User::find($ticket['user_id'])?->notify(new MessageNotification(null, MessageType::System, $this->user->username, 'Был открыт тикет №' . $id));
		}

		return $this->index();
	}

	public function close($id)
	{
		if (!$this->access->canWriteController(self::CODE, 'admin')) {
			throw new \Exception('Access denied');
		}

		if (!$id) {
			$this->message('Не заполнены все поля', 'Ошибка', '/admin/support/', 3);
		}

		$ticket = $this->db->query("SELECT id, text, user_id FROM support WHERE id = '" . $id . "';")->fetch();

		if (isset($ticket['id'])) {
			$newtext = $ticket['text'] . '<br><br><hr>' . $this->user->username . ' закрыл тикет в ' . date("j. M Y H:i:s", time());

			$this->db->query("UPDATE support SET text = '" . addslashes($newtext) . "', status = '0' WHERE id = '" . $id . "'");

			User::find($ticket['user_id'])?->notify(new MessageNotification(null, MessageType::System, $this->user->username, 'Тикет №' . $id . ' закрыт'));
		}

		return $this->index();
	}
}
