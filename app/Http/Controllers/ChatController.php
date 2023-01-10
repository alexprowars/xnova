<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Controller;
use App\Events\ChatMessage;
use App\Events\ChatPrivateMessage;
use App\Exceptions\Exception;
use App\Models\Chat;

class ChatController extends Controller
{
	public function index()
	{
		$this->setTitle('Межгалактический чат');
		$this->showTopPanel(false);

		return [];
	}

	public function sendMessage(Request $request)
	{
		$message = $request->post('message', null);

		if (!$message) {
			throw new Exception('Введите текст сообщения');
		}

		$chatMessage = Chat::query()->create([
			'user_id' => Auth::id(),
			'message' => $message,
		]);

		$parsedMessage = $chatMessage->parse();

		if ($parsedMessage['private']) {
			foreach ($parsedMessage['toi'] as $userId) {
				event(new ChatPrivateMessage($userId, $parsedMessage));
			}

			event(new ChatPrivateMessage(Auth::id(), $parsedMessage));
		} else {
			event(new ChatMessage($chatMessage->parse()));
		}

		return [
			'message' => $parsedMessage,
		];
	}

	public function last()
	{
		$items = Chat::query()
			->orderByDesc('id')
			->limit(30);

		$lastMessage = Chat::query()
			->orderByDesc('id')
			->first(['id'])->id ?? 0;

		if ($lastMessage) {
			$items->where(function ($query) use ($lastMessage) {
				$query->where('id', '>=', $lastMessage - 30)
					->orWhere('created_at', '>', Carbon::now()->subMinutes(30));
			});
		}

		$items = $items->get();

		$result = [];

		foreach ($items as $item) {
			$result[] = $item->parse();
		}

		return ['messages' => array_reverse($result)];
	}
}
