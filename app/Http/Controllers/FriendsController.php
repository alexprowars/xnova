<?php

namespace App\Http\Controllers;

use App\Engine\Enums\MessageType;
use App\Exceptions\Exception;
use App\Exceptions\RedirectException;
use App\Models;
use App\Models\Friend;
use App\Models\User;
use App\Notifications\MessageNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FriendsController extends Controller
{
	public function index(Request $request)
	{
		$parse = [];

		if ($request->get('requests')) {
			$parse['title'] = $request->has('my') ? 'Мои запросы' : 'Другие запросы';
		}

		$parse['items'] = [];
		$parse['isMy'] = $request->has('my');

		$items = Models\Friend::query()
			->orderByDesc('id')
			->where('ignore', false);

		if ($request->get('requests')) {
			$items->where('active', false);

			if ($request->get('my')) {
				$items->whereBelongsTo($this->user);
			} else {
				$items->whereBelongsTo($this->user, 'friend');
			}
		} else {
			$items
				->where('active', false)
				->where(function (Builder $query) {
					$query->whereBelongsTo($this->user)->whereBelongsTo($this->user, 'friend');
				});
		}

		$items = $items->get();

		foreach ($items as $item) {
			$userId = $item->friend_id == $this->user->id ? $item->user_id : $item->friend_id;

			$user = Models\User::find($userId);

			if (!$user) {
				$item->delete();
				continue;
			}

			$row = [
				'id' => $item->id,
				'online' => 0,
				'message' => $item->message,
				'user' => [
					'id' => $user->id,
					'name' => $user->username,
					'alliance' => [
						'id' => $user->alliance_id,
						'name' => $user->alliance_name,
					],
					'galaxy' => $user->galaxy,
					'system' => $user->system,
					'planet' => $user->planet,
				],
			];

			if (!$request->get('requests')) {
				if ($user->onlinetime?->timestamp > time() - 59 * 60) {
					$row['online'] = floor((time() - $user->onlinetime->timestamp) / 60);
				} else {
					$row['online'] = 60;
				}
			}

			$parse['items'][] = $row;
		}

		return $parse;
	}

	public function new(int $userId)
	{
		$user = User::find($userId);

		if (!$user) {
			throw new Exception('Друг не найден');
		}

		if ($user->id == $this->user->id) {
			throw new Exception('Нельзя дружить сам с собой');
		}

		return [
			'id' => $user->id,
			'username' => $user->username,
		];
	}

	public function create(int $userId, Request $request)
	{
		$user = User::find($userId);

		if (!$user) {
			throw new Exception('Друг не найден');
		}

		if ($user->id == $this->user->id) {
			throw new Exception('Нельзя дружить сам с собой');
		}

		$friend = Friend::query()
			->where(function (Builder $query) use ($userId) {
				$query->where('user_id', $userId)->whereBelongsTo($this->user, 'friend');
			})
			->orWhere(function (Builder $query) use ($userId) {
				$query->whereBelongsTo($this->user)->where('friend_id', $userId);
			})
			->exists();

		if ($friend) {
			throw new Exception('Запрос дружбы был уже отправлен ранее');
		}

		$message = strip_tags($request->post('message', ''));

		if (mb_strlen($message) > 5000) {
			throw new Exception('Максимальная длинна сообщения 5000 символов!');
		}

		Friend::create([
			'user_id' => $this->user->id,
			'friend_id' => $user->id,
			'active' => false,
			'message' => $message,
		]);

		$user->notify(new MessageNotification(null, MessageType::System, 'Запрос дружбы', 'Игрок ' . $this->user->username . ' отправил вам запрос на добавление в друзья. <a href="/friends/requests"><< просмотреть >></a>'));
	}

	public function delete(int $id)
	{
		$friend = Models\Friend::find($id);

		if (!$friend) {
			throw new Exception('Заявка не найдена');
		}

		if ($friend->friend_id == $this->user->id) {
			$friend->delete();

			throw new RedirectException('/friends/requests', 'Заявка отклонена');
		} elseif ($friend->user_id == $this->user->id) {
			$friend->delete();

			throw new RedirectException('/friends/requests/my', 'Заявка удалена');
		}

		throw new Exception('Заявка не найдена');
	}

	public function approve(int $id)
	{
		$friend = Models\Friend::find($id);

		if (!$friend) {
			throw new Exception('Заявка не найдена');
		}

		if (!($friend->friend_id == $this->user->id && !$friend->active)) {
			throw new Exception('Заявка не найдена');
		}

		$friend->active = 1;
		$friend->update();
	}
}
