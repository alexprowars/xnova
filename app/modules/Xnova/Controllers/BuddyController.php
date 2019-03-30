<?php

namespace Xnova\Controllers;

use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\RedirectException;
use Xnova\Request;
use Xnova\User;
use Xnova\Controller;
use Xnova\Models;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

/**
 * @RoutePrefix("/buddy")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class BuddyController extends Controller
{
	public function newAction ($userId)
	{
		/** @var Models\User $user */
		$user = Models\User::query()
			->columns(['id', 'username'])
			->where('id = :user:')
			->bind(['user' => (int) $userId])
			->execute()->getFirst();

		if (!$user)
			throw new ErrorException('Друг не найден');

		if ($this->request->isPost())
		{
			$buddy = Models\Buddy::query()
				->columns(['id'])
				->where('(sender = :current: AND owner = :user:) OR (sender = :user: AND owner = :current:)')
				->bind(['user' => $userId, 'current' => $this->user->id])
				->limit(1)
				->execute()->getFirst();

			if ($buddy)
				throw new ErrorException('Запрос дружбы был уже отправлен ранее');

			$text = strip_tags($this->request->getPost('text', 'string', ''));

			if (mb_strlen($text) > 5000)
				throw new ErrorException('Максимальная длинна сообщения 5000 символов!');

			$this->db->insertAsDict('game_buddy', [
				'sender' => $this->user->id,
				'owner' => $user->id,
				'active' => 0,
				'text' => $text
			]);

			User::sendMessage($user->id, 0, time(), 1, 'Запрос дружбы', 'Игрок '.$this->user->username.' отправил вам запрос на добавление в друзья. <a href="/buddy/requests/"><< просмотреть >></a>');

			throw new RedirectException('Запрос отправлен', '/buddy/');
		}

		if ($user->id == $this->user->id)
			throw new ErrorException('Нельзя дружить сам с собой');

		Request::addData('page', [
			'id' => $user->id,
			'username' => $user->username,
		]);

		$this->tag->setTitle('Друзья');
		$this->showTopPanel(false);
	}

	public function requestsAction ($isMy = false)
	{
		if ($isMy !== false)
			$isMy = true;

		$this->indexAction(true, $isMy);
	}

	public function deleteAction ($id)
	{
		$buddy = Models\Buddy::findFirst((int) $id);

		if (!$buddy)
			throw new ErrorException('Заявка не найдена');

		if ($buddy->owner == $this->user->id)
		{
			$this->db->delete('game_buddy', 'id = ?', [$buddy->id]);

			throw new RedirectException('Заявка отклонена', '/buddy/requests/');
		}
		elseif ($buddy->sender == $this->user->id)
		{
			$this->db->delete('game_buddy', 'id = ?', [$buddy->id]);

			throw new RedirectException('Заявка удалена', '/buddy/requests/my/');
		}
		else
			throw new ErrorException('Заявка не найдена');
	}

	public function approveAction ($id)
	{
		$buddy = Models\Buddy::findFirst((int) $id);

		if (!$buddy)
			throw new ErrorException('Заявка не найдена');

		if (!($buddy->owner == $this->user->id && $buddy->active == 0))
			throw new ErrorException('Заявка не найдена');

		$buddy->active = 1;
		$buddy->update();

		throw new RedirectException('', '/buddy/');
	}
	
	public function indexAction ($isRequests = false, $isMy = false)
	{
		if ($isRequests)
			$parse['title'] = $isMy ? 'Мои запросы' : 'Другие запросы';

		$parse['items'] = [];
		$parse['isMy'] = $isMy;

		$items = Models\Buddy::query()
			->orderBy('id DESC')
			->bind(['user' => $this->user->id]);

		if ($isRequests)
		{
			if ($isMy)
				$items->where('active = 0 AND ignor = 0 AND sender = :user:');
			else
				$items->where('active = 0 AND ignor = 0 AND owner = :user:');
		}
		else
			$items->where('active = 1 AND ignor = 0 AND (sender = :user: OR owner = :user:)');

		$items = $items->execute();

		/** @var Models\Buddy $item */
		foreach ($items as $item)
		{
			$userId = ($item->owner == $this->user->id) ? $item->sender : $item->owner;

			/** @var Models\User $user */
			$user = Models\User::query()
				->columns(['id', 'username', 'galaxy', 'system', 'planet', 'onlinetime', 'ally_id', 'ally_name'])
				->where('id = :user:')
				->bind(['user' => $userId])
				->execute()->getFirst();

			if (!$user)
			{
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

			if (!$isRequests)
			{
				if ($user->onlinetime > (time() - 59 * 60))
					$row['online'] = floor((time() - $user->onlinetime) / 60);
				else
					$row['online'] = 60;
			}

			$parse['items'][] = $row;
		}

		Request::addData('page', $parse);

		$this->tag->setTitle('Список друзей');
	}
}