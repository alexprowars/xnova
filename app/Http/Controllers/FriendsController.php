<?php

namespace App\Http\Controllers;

use App\Engine\Enums\MessageType;
use App\Engine\Messages\Types\FriendsRequestMessage;
use App\Exceptions\Exception;
use App\Exceptions\PageException;
use App\Models;
use App\Models\Friend;
use App\Models\User;
use App\Notifications\SystemMessage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;

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

		return Inertia::render('Friends/List', [
			'items' => $result,
		]);
	}

	public function requests(Request $request)
	{
		$isMyRequests = str_contains($request->path(), '/my');

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

		return Inertia::render('Friends/Requests', [
			'items' => $result,
			'isMy' => $isMyRequests,
		]);
	}

	public function new(int $userId)
	{
		$user = User::find($userId);

		if (!$user) {
			throw new PageException(__('main.friends_not_found'));
		}

		if ($user->is($this->user)) {
			throw new PageException(__('main.friends_self_request'));
		}

		return Inertia::render('Friends/New', [
			'id' => $user->id,
			'username' => $user->username,
		]);
	}

	public function create(int $userId, Request $request)
	{
		$user = User::find($userId);

		if (!$user) {
			throw new Exception(__('main.friends_not_found'));
		}

		if ($user->id == $this->user->id) {
			throw new Exception(__('main.friends_self_request'));
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
			throw new Exception(__('main.friends_request_already_sent'));
		}

		$message = strip_tags($request->post('message', ''));

		if (mb_strlen($message) > 250) {
			throw new Exception(__('main.friends_message_too_long'));
		}

		Friend::create([
			'user_id' => $this->user->id,
			'friend_id' => $user->id,
			'active' => false,
			'message' => $message,
		]);

		$user->notify(
			new SystemMessage(MessageType::System, new FriendsRequestMessage(['name' => $this->user->username]))
		);

		return to_route('friends');
	}

	public function delete(int $id): void
	{
		$friend = Models\Friend::find($id);

		if (!$friend) {
			throw new Exception(__('main.friends_request_not_found'));
		}

		if ($friend->friend_id == $this->user->id || $friend->user_id == $this->user->id) {
			$friend->delete();
		} else {
			throw new Exception(__('main.friends_request_not_found'));
		}
	}

	public function approve(int $id)
	{
		$friend = Models\Friend::find($id);

		if (!$friend) {
			throw new Exception(__('main.friends_request_not_found'));
		}

		if ($friend->friend_id != $this->user->id || $friend->active) {
			throw new Exception(__('main.friends_request_not_found'));
		}

		$friend->active = true;
		$friend->update();

		return to_route('friends');
	}
}
