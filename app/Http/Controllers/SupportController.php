<?php

namespace App\Http\Controllers;

use App\Engine\Enums\MessageType;
use App\Exceptions\Exception;
use App\Helpers;
use App\Models\SupportTicket;
use App\Models\User;
use App\Notifications\MessageNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupportController extends Controller
{
	public function index(): array
	{
		$tickets = SupportTicket::query()
			->whereBelongsTo($this->user)
			->latest()
			->get();

		$items = [];

		foreach ($tickets as $ticket) {
			$items[] = [
				'id' => $ticket->id,
				'status' => $ticket->status,
				'subject' => $ticket->subject,
				'created_at' => $ticket->created_at?->utc()->toAtomString(),
				'updated_at' => $ticket->updated_at?->utc()->toAtomString(),
			];
		}

		return $items;
	}

	public function create(Request $request): void
	{
		$message = $request->post('message');
		$subject = $request->post('subject');

		if (empty($message) || empty($subject)) {
			throw new Exception('Не заполнены все поля');
		}

		$ticket = new SupportTicket();
		$ticket->user()->associate($this->user);
		$ticket->subject = Str::sanitize($subject);
		$ticket->message = Str::sanitize($message);
		$ticket->status = 1;

		if (!$ticket->save()) {
			throw new Exception('Не удалось создать тикет');
		}
	}

	public function answer(int $id, Request $request): void
	{
		$ticket = SupportTicket::findOne($id);

		if (!$ticket) {
			throw new Exception('Тикет не найден');
		}

		$message = $request->post('message');

		if (empty($message)) {
			throw new Exception('Не заполнены все поля');
		}

		$ticket->messages()->make([
			'message' => Str::sanitize($message),
		])
		->user()->associate($this->user)
		->save();

		$ticket->status = 3;
		$ticket->updateTimestamps();
		$ticket->save();

		$usersId = array_unique(array_merge(
			[1],
			$ticket->messages()->whereNot('user_id', $this->user->id)->pluck('user_id')->toArray()
		));

		$notifyMessage = '<a href="/support/' . $ticket->id . '" target="_blank">Поступил ответ на тикет №' . $id . '</a>';

		foreach ($usersId as $userId) {
			User::findOne($userId)?->notify(
				new MessageNotification(null, MessageType::System, $this->user->username, $notifyMessage)
			);
		}
	}

	public function info(int $id): array
	{
		$ticket = SupportTicket::query()
			->with(['messages', 'messages.user'])
			->whereBelongsTo($this->user)
			->findOne($id);

		if (!$ticket) {
			throw new Exception('Тикет не найден');
		}

		$result = [
			'id' => $ticket->id,
			'status' => $ticket->status,
			'subject' => $ticket->subject,
			'created_at' => $ticket->created_at?->utc()->toAtomString(),
			'updated_at' => $ticket->updated_at?->utc()->toAtomString(),
			'message' => $ticket->message,
			'messages' => [],
		];

		foreach ($ticket->messages as $message) {
			$result['messages'][] = [
				'date' => $message->created_at?->utc()->toAtomString(),
				'user' => $message->user?->username ?? null,
				'user_id' => $message->user?->id ?? null,
				'message' => $message->message,
			];
		}

		return $result;
	}
}
