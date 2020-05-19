<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace Xnova\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Xnova\Controller;
use Xnova\Events\ChatMessage;
use Xnova\Events\ChatPrivateMessage;
use Xnova\Models\Chat;

class ChatController extends Controller
{
	public function index()
	{
		$this->setTitle('Межгалактический чат');
		$this->showTopPanel(false);

		return $this->history(true);
	}

	public function sendMessage(Request $request)
	{
		$message = $request->post('message', null);

		if ($message) {
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
		}
	}

	public function history(bool $last = false)
	{
		$items = Chat::query()
			->orderByDesc('id')
			->limit($last ? 15 : 50);

		if ($last) {
			$lastMessage = Chat::query()
				->orderByDesc('id')
				->first(['id'])->id ?? 0;

			if ($lastMessage) {
				$items->where(function ($query) use ($lastMessage) {
					$query->where('id', '>=', $lastMessage - 15)
						->orWhere('created_at', '>', Carbon::now()->subMinutes(30));
				});
			}
		} else {
			$items->where('message', 'not like', '%приватно [%');
		}

		$items = $items->get();

		$result = [];

		foreach ($items as $item) {
			$result[] = $item->parse();
		}

		return ['messages' => array_reverse($result)];
	}
}
