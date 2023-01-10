<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Exceptions\ErrorException;
use App\Exceptions\RedirectException;
use App\User;
use App\Controller;
use App\Models;

class BuddyController extends Controller
{
	public function newAction(Request $request, $userId)
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
				->select(['id'])
				->where(function (Builder $query) use ($userId) {
					$query->where('sender', $userId)
						->where('owner', $this->user->id);
				})
				->orWhere(function (Builder $query) use ($userId) {
					$query->where('owner', $userId)
						->where('sender', $this->user->id);
				})
				->first();

			if ($buddy) {
				throw new ErrorException('Запрос дружбы был уже отправлен ранее');
			}

			$text = strip_tags($request->post('text', ''));

			if (mb_strlen($text) > 5000) {
				throw new ErrorException('Максимальная длинна сообщения 5000 символов!');
			}

			Models\Friend::query()->insert([
				'sender' => $this->user->id,
				'owner' => $user->id,
				'active' => 0,
				'text' => $text,
			]);

			User::sendMessage($user->id, 0, time(), 1, 'Запрос дружбы', 'Игрок ' . $this->user->username . ' отправил вам запрос на добавление в друзья. <a href="/buddy/requests/"><< просмотреть >></a>');

			throw new RedirectException('Запрос отправлен', '/buddy/');
		}

		if ($user->id == $this->user->id) {
			throw new ErrorException('Нельзя дружить сам с собой');
		}

		$this->setTitle('Друзья');
		$this->showTopPanel(false);

		return [
			'id' => $user->id,
			'username' => $user->username,
		];
	}

	public function requestsAction($isMy = false)
	{
		if ($isMy !== false) {
			$isMy = true;
		}

		$this->index(true, $isMy);
	}

	public function deleteAction(int $id)
	{
		$friend = Models\Friend::query()->find($id);

		if (!$friend) {
			throw new ErrorException('Заявка не найдена');
		}

		if ($friend->owner == $this->user->id) {
			$friend->delete();

			throw new RedirectException('Заявка отклонена', '/buddy/requests/');
		} elseif ($friend->sender == $this->user->id) {
			$friend->delete();

			throw new RedirectException('Заявка удалена', '/buddy/requests/my/');
		} else {
			throw new ErrorException('Заявка не найдена');
		}
	}

	public function approveAction(int $id)
	{
		$friend = Models\Friend::query()->find($id);

		if (!$friend) {
			throw new ErrorException('Заявка не найдена');
		}

		if (!($friend->owner == $this->user->id && $friend->active == 0)) {
			throw new ErrorException('Заявка не найдена');
		}

		$friend->active = 1;
		$friend->update();

		throw new RedirectException('', '/buddy/');
	}

	public function index($isRequests = false, $isMy = false)
	{
		if ($isRequests) {
			$parse['title'] = $isMy ? 'Мои запросы' : 'Другие запросы';
		}

		$parse['items'] = [];
		$parse['isMy'] = $isMy;

		$items = Models\Friend::query()
			->orderBy('id', 'DESC')
			->where('ignor', 0);

		if ($isRequests) {
			$items->where('active', 0);

			if ($isMy) {
				$items->where('sender', $this->user->id);
			} else {
				$items->where('owner', $this->user->id);
			}
		} else {
			$items->where('active', 0)
			->where(function (Builder $query) {
				$query->where('sender', $this->user->id)
					->where('owner', $this->user->id);
			});
		}

		$items = $items->get();

		foreach ($items as $item) {
			$userId = ($item->owner == $this->user->id) ? $item->sender : $item->owner;

			$user = Models\User::query()
				->select(['id', 'username', 'galaxy', 'system', 'planet', 'onlinetime', 'ally_id', 'ally_name'])
				->where('id', $userId)
				->first();

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
						'id' => (int) $user->ally_id,
						'name' => $user->ally_name
					],
					'galaxy' => (int) $user->galaxy,
					'system' => (int) $user->system,
					'planet' => (int) $user->planet,
				]
			];

			if (!$isRequests) {
				if ($user->onlinetime > (time() - 59 * 60)) {
					$row['online'] = floor((time() - $user->onlinetime) / 60);
				} else {
					$row['online'] = 60;
				}
			}

			$parse['items'][] = $row;
		}

		$this->setTitle('Список друзей');

		return $parse;
	}
}
