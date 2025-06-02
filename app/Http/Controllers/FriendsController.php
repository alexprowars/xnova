<?php

namespace App\Http\Controllers;

use App\Engine\Enums\MessageType;
use App\Exceptions\Exception;
use App\Models;
use App\Models\Friend;
use App\Models\User;
use App\Notifications\MessageNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FriendsController extends Controller
{
	public function index()
	{
		$result = [];

		$items = Models\Friend::query()
			->orderByDesc('id')
			->where('ignore', false)
			->where('active', true)
			->where(function (Builder $query) {
				$query->whereBelongsTo($this->user)->orWhereBelongsTo($this->user, 'friend');
			})
			->get();

		foreach ($items as $item) {
			$userId = $item->friend_id == $this->user->id ? $item->user_id : $item->friend_id;

			$user = Models\User::find($userId);

			if (!$user) {
				$item->delete();
				continue;
			}

			$onlineDiff = (int) $user->onlinetime->diffInMinutes();

			$result[] = [
				'id' => $item->id,
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
				'online' => match (true) {
					$onlineDiff < 10 => 1,
					$onlineDiff < 20 => 2,
					default => 0,
				},
			];
		}

		return $result;
	}

	public function requests(Request $request)
	{
		$isMyRequests = $request->get('my', 'N') == 'Y';

		$result = [];

		$items = Models\Friend::query()
			->orderByDesc('id')
			->where('ignore', false)
			->where('active', false)
			->when(
				$isMyRequests,
				fn(Builder $query) => $query->whereBelongsTo($this->user),
				fn(Builder $query) => $query->whereBelongsTo($this->user, 'friend')
			)
			->get();

		foreach ($items as $item) {
			$userId = $item->friend_id == $this->user->id ? $item->user_id : $item->friend_id;

			$user = Models\User::find($userId);

			if (!$user) {
				$item->delete();
				continue;
			}

			$result[] = [
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
		}

		return $result;
	}

	public function new(int $userId)
	{
		$user = User::find($userId);

		if (!$user) {
			throw new Exception('Друг не найден');
		}

		if ($user->is($this->user)) {
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

		if ($friend->friend_id == $this->user->id || $friend->user_id == $this->user->id) {
			$friend->delete();
		} else {
			throw new Exception('Заявка не найдена');
		}
	}

	public function approve(int $id)
	{
		$friend = Models\Friend::find($id);

		if (!$friend) {
			throw new Exception('Заявка не найдена');
		}

		if ($friend->friend_id != $this->user->id || $friend->active) {
			throw new Exception('Заявка не найдена');
		}

		$friend->active = true;
		$friend->update();
	}
}
