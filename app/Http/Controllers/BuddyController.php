<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Exceptions\ErrorException;
use App\Exceptions\RedirectException;
use App\Models\User;
use App\Controller;
use App\Models;

class BuddyController extends Controller
{
	public function new(Request $request, $userId)
	{
		$user = Models\User::query()
			->select(['id', 'username'])
			->where('id', $userId)
			->first();

		if (!$user) {
			throw new ErrorException('Друг не найден');
		}

		if ($request->isMethod('post')) {
			$buddy = Models\Friend::query()
				->where(function (Builder $query) use ($userId) {
					$query->where('user_id', $userId)->where('friend_id', $this->user->id);
				})
				->orWhere(function (Builder $query) use ($userId) {
					$query->where('user_id', $userId)->where('friend_id', $this->user->id);
				})
				->exists();

			if ($buddy) {
				throw new ErrorException('Запрос дружбы был уже отправлен ранее');
			}

			$text = strip_tags($request->post('text', ''));

			if (mb_strlen($text) > 5000) {
				throw new ErrorException('Максимальная длинна сообщения 5000 символов!');
			}

			Models\Friend::create([
				'user_id' => $this->user->id,
				'friend_id' => $user->id,
				'active' => false,
				'message' => $text,
			]);

			User::sendMessage($user->id, 0, time(), 2, 'Запрос дружбы', 'Игрок ' . $this->user->username . ' отправил вам запрос на добавление в друзья. <a href="/buddy/requests/"><< просмотреть >></a>');

			throw new RedirectException('Запрос отправлен', '/buddy');
		}

		if ($user->id == $this->user->id) {
			throw new ErrorException('Нельзя дружить сам с собой');
		}

		return [
			'id' => $user->id,
			'username' => $user->username,
		];
	}

	public function requests($isMy = false)
	{
		if ($isMy !== false) {
			$isMy = true;
		}

		$this->index(true, $isMy);
	}

	public function delete(int $id)
	{
		$friend = Models\Friend::find($id);

		if (!$friend) {
			throw new ErrorException('Заявка не найдена');
		}

		if ($friend->friend_id == $this->user->id) {
			$friend->delete();

			throw new RedirectException('Заявка отклонена', '/buddy/requests/');
		} elseif ($friend->user_id == $this->user->id) {
			$friend->delete();

			throw new RedirectException('Заявка удалена', '/buddy/requests/my');
		} else {
			throw new ErrorException('Заявка не найдена');
		}
	}

	public function approve(int $id)
	{
		$friend = Models\Friend::find($id);

		if (!$friend) {
			throw new ErrorException('Заявка не найдена');
		}

		if (!($friend->friend_id == $this->user->id && !$friend->active)) {
			throw new ErrorException('Заявка не найдена');
		}

		$friend->active = 1;
		$friend->update();

		throw new RedirectException('', '/buddy');
	}

	public function index($isRequests = false, $isMy = false)
	{
		if ($isRequests) {
			$parse['title'] = $isMy ? 'Мои запросы' : 'Другие запросы';
		}

		$parse['items'] = [];
		$parse['isMy'] = $isMy;

		$items = Models\Friend::query()
			->orderByDesc('id')
			->where('ignore', 0);

		if ($isRequests) {
			$items->where('active', 0);

			if ($isMy) {
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
			$userId = ($item->friend_id == $this->user->id) ? $item->user_id : $item->friend_id;

			$user = Models\User::find($userId);

			if (!$user) {
				$item->delete();
				continue;
			}

			$row = [
				'id' => (int) $item->id,
				'online' => 0,
				'text' => $item->text,
				'user' => [
					'id' => (int) $user->id,
					'name' => $user->username,
					'alliance' => [
						'id' => (int) $user->alliance_id,
						'name' => $user->alliance_name
					],
					'galaxy' => (int) $user->galaxy,
					'system' => (int) $user->system,
					'planet' => (int) $user->planet,
				]
			];

			if (!$isRequests) {
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
}
