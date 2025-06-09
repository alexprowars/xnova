<?php

namespace App\Http\Controllers;

use App\Events\ChatMessage;
use App\Events\ChatPrivateMessage;
use App\Exceptions\Exception;
use App\Http\Resources;
use App\Models\Chat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ChatController extends Controller
{
	public function send(Request $request)
	{
		$message = $request->post('message');

		if (empty($message)) {
			throw new Exception('Введите текст сообщения');
		}

		$chatMessage = Chat::create([
			'user_id' => Auth::id(),
			'message' => $message,
			'date' => now(),
		]);

		$parsedMessage = Resources\ChatMessage::make($chatMessage)->resolve();

		if ($parsedMessage['private']) {
			foreach ($parsedMessage['toi'] as $userId) {
				event(new ChatPrivateMessage($userId, $parsedMessage));
			}

			event(new ChatPrivateMessage(auth()->id(), $parsedMessage));
		} else {
			event(new ChatMessage($parsedMessage));
		}

		Cache::delete('chat.cache');
	}

	public function last()
	{
		$items = Chat::query()
			->with(['user'])
			->orderByDesc('id')
			->limit(30);

		$lastMessage = Chat::query()
			->orderByDesc('id')
			->value('id') ?? 0;

		if ($lastMessage) {
			$items->where(function ($query) use ($lastMessage) {
				$query->where('id', '>=', $lastMessage - 30)
					->orWhere('date', '>', Carbon::now()->subMinutes(30));
			});
		}

		$items = $items->get();

		return Resources\ChatMessage::collection($items->reverse());
	}
}
