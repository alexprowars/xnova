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

class BuddyController extends Controller
{
	public function index(Request $request)
	{
		$parse = [];

		if ($request->has('requests')) {
			$parse['title'] = $request->has('my') ? 'Мои запросы' : 'Другие запросы';
		}

		$parse['items'] = [];
		$parse['isMy'] = $request->has('my');

		$items = Models\Friend::query()
			->orderByDesc('id')
			->where('ignore', 0);

		if ($request->has('requests')) {
			$items->where('active', 0);

			if ($request->has('my')) {
				$items->where('user_id', $this->user->id);
			} else {
				$items->where('friend_id', $this->user->id);
			}
		} else {
			$items
				->where('active', 0)
				->where(function (Builder $query) {
					$query->where('user_id', $this->user->id)->where('friend_id', $this->user->id);
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
					'id' => (int) $user->id,
					'name' => $user->username,
					'alliance' => [
						'id' => (int) $user->alliance_id,
						'name' => $user->alliance_name,
					],
					'galaxy' => (int) $user->galaxy,
					'system' => (int) $user->system,
					'planet' => (int) $user->planet,
				],
			];

			if (!$request->has('requests')) {
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

		$buddy = Friend::query()
			->where(function (Builder $query) use ($userId) {
				$query->where('user_id', $userId)->where('friend_id', $this->user->id);
			})
			->orWhere(function (Builder $query) use ($userId) {
				$query->where('user_id', $this->user->id)->where('friend_id', $userId);
			})
			->exists();

		if ($buddy) {
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

		$user->notify(new MessageNotification(null, MessageType::System, 'Запрос дружбы', 'Игрок ' . $this->user->username . ' отправил вам запрос на добавление в друзья. <a href="/buddy/requests"><< просмотреть >></a>'));
	}

	public function delete(int $id)
	{
		$friend = Models\Friend::find($id);

		if (!$friend) {
			throw new Exception('Заявка не найдена');
		}

		if ($friend->friend_id == $this->user->id) {
			$friend->delete();

			throw new RedirectException('/buddy/requests', 'Заявка отклонена');
		} elseif ($friend->user_id == $this->user->id) {
			$friend->delete();

			throw new RedirectException('/buddy/requests/my', 'Заявка удалена');
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
