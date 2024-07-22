<?php

namespace App\Http\Controllers;

use App\Engine\Enums\MessageType;
use App\Exceptions\Exception;
use App\Exceptions\RedirectException;
use App\Helpers;
use App\Models\Support;
use App\Models\User;
use App\Notifications\MessageNotification;
use Illuminate\Http\Request;

class SupportController extends Controller
{
	public function index()
	{
		$items = [];

		$tickets = Support::query()
			->whereBelongsTo($this->user)
			->orderByDesc('created_at')
			->get();

		foreach ($tickets as $ticket) {
			$items[] = [
				'id' => (int) $ticket->id,
				'status' => (int) $ticket->status,
				'subject' => $ticket->subject,
				'date' => $ticket->created_at?->utc()->toAtomString(),
			];
		}

		return response()->state($items);
	}

	public function add(Request $request)
	{
		$message = $request->post('message', '');
		$subject = $request->post('subject', '');

		if (empty($message) || empty($subject)) {
			throw new Exception('Не заполнены все поля');
		}

		$ticket = new Support();
		$ticket->user_id = $this->user->id;
		$ticket->subject = Helpers::checkString($subject);
		$ticket->message = Helpers::checkString($message);
		$ticket->status = 1;

		if (!$ticket->save()) {
			throw new Exception('Не удалось создать тикет');
		}
	}

	public function answer(int $id, Request $request)
	{
		if (!$id) {
			throw new RedirectException('/support', 'Не задан ID тикета');
		}

		$message = $request->post('message', '');

		if (empty($message)) {
			throw new Exception('Не заполнены все поля');
		}

		$ticket = Support::find($id);

		if (!$ticket) {
			throw new RedirectException('/support', 'Тикет не найден');
		}

		$message = $ticket->message . '<hr>' . $this->user->username . ' ответил в ' . date("d.m.Y H:i:s") . ':<br>' . Helpers::checkString($message);

		$ticket->message = $message;
		$ticket->status = 3;
		$ticket->update();

		User::find(1)?->notify(new MessageNotification(null, MessageType::System, $this->user->username, 'Поступил ответ на тикет №' . $id));
	}

	public function info($id)
	{
		$ticket = Support::query()
			->where('user_id', $this->user->id)
			->where('id', $id)
			->first();

		if (!$ticket) {
			throw new Exception('Тикет не найден');
		}

		return response()->state([
			'id' => $ticket->id,
			'status' => $ticket->status,
			'subject' => $ticket->subject,
			'date' => $ticket->created_at?->utc()->toAtomString(),
			'message' => $ticket->message,
		]);
	}
}
