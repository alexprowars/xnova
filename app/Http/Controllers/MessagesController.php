<?php

namespace App\Http\Controllers;

use App\Engine\Enums\MessageType;
use App\Engine\Game;
use App\Exceptions\Exception;
use App\Format;
use App\Models\Message;
use App\Models\User;
use App\Notifications\MessageNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class MessagesController extends Controller
{
	public function index(Request $request)
	{
		$types = [1, 2, 3, 4, 5, 6, 15, 99, 100, 101];
		$limits = [5, 10, 25, 50, 100, 200];

		$category = 100;

		if (Session::has('m_cat')) {
			$category = (int) Session::get('m_cat');
		}

		if ($request->query('category')) {
			$category = (int) $request->query('category', 100);
		}

		if (!in_array($category, $types)) {
			$category = 100;
		}

		$limit = 10;

		if (Session::has('m_limit')) {
			$limit = (int) Session::get('m_limit');
		}

		if ($request->query('limit')) {
			$limit = (int) $request->query('limit', 10);
		}

		if (!in_array($limit, $limits)) {
			$limit = 10;
		}

		$page = (int) $request->query('p', 0);

		if ($page <= 0) {
			$page = 1;
		}

		if (!Session::has('m_limit') || Session::get('m_limit') != $limit) {
			Session::put('m_limit', $limit);
		}

		if (!Session::has('m_cat') || Session::get('m_cat') != $category) {
			Session::put('m_cat', $category);
		}

		$parse = [];
		$parse['limit'] = $limit;
		$parse['category'] = $category;

		if ($this->user->messages > 0) {
			$this->user->messages = 0;
			$this->user->update();
		}

		$messages = Message::query()
			->select(['messages.id', 'type', 'date', 'message', 'from_id'])
			->orderByDesc('date');

		if ($category == 101) {
			$messages->addSelect(DB::raw('CONCAT(users.username, \' [\', users.galaxy,\':\', users.system,\':\', users.planet, \']\') as subject'))
				->leftJoin('users', 'users.id', '=', 'user_id')
				->whereBelongsTo($this->user, 'from');
		} else {
			$messages->addSelect('subject')
				->whereBelongsTo($this->user)
				->when(
					$category < 100,
					fn(Builder $query) => $query->where('type', $category),
				);
		}

		$paginator = $messages->paginate($limit, page: $page);

		$items = $paginator->items();
		$parse['items'] = [];

		/** @var Message $item */
		foreach ($items as $item) {
			if (preg_match('/#DATE\|(.*?)\|(.*?)#/i', $item->message, $match)) {
				$item->message = str_replace($match[0], Game::datezone(trim($match[1]), (int) $match[2]), $item->message);
			}

			$parse['items'][] = [
				'id' => $item->id,
				'type' => $item->type,
				'date' => $item->date->utc()->toAtomString(),
				'from' => $item->from_id,
				'subject' => $item->subject ?? '',
				'message' => str_replace(["\r\n", "\n", "\r"], '<br>', stripslashes($item->message)),
			];
		}

		$parse['pagination'] = [
			'total' => $paginator->total(),
			'limit' => $limit,
			'page' => $paginator->currentPage(),
		];

		return $parse;
	}

	public function write(int $userId, Request $request)
	{
		if (!$userId) {
			throw new Exception(__('messages.mess_no_ownerid'));
		}

		$user = User::find($userId);

		if (!$user) {
			throw new Exception(__('messages.mess_no_owner'));
		}

		$page = [
			'id' => $user->id,
			'to' => $user->username . ' [' . $user->galaxy . ':' . $user->system . ':' . $user->planet . ']',
			'message' => '',
		];

		if ($request->query('quote')) {
			$message = Message::query()
				->whereKey($request->query('quote'))
				->where(function (Builder $query) {
					$query->whereBelongsTo($this->user)
						->orWhereBelongsTo($this->user, 'from');
				})
				->value('message');

			if ($message) {
				$page['message'] = '[quote]' . preg_replace('/<br(\s*)?\/?>/iu', '', $message) . '[/quote]';
			}
		}

		return $page;
	}

	public function send(int $userId, Request $request)
	{
		$user = User::find($userId);

		if (!$user) {
			throw new Exception(__('messages.mess_no_owner'));
		}

		$message = $request->post('message', '');

		if (empty($message)) {
			throw new Exception(__('messages.mess_no_text'));
		}

		if ($this->user->message_block?->isFuture()) {
			throw new Exception(__('messages.mess_similar'));
		}

		if ($this->user->lvl_minier == 1 && $this->user->lvl_raid == 1 && $this->user->created_at?->addDay()->isFuture()) {
			$lastSend = Message::query()
				->whereBelongsTo($this->user)
				->where('date', '>', now()->subMinute())
				->count();

			if ($lastSend > 0) {
				throw new Exception(__('messages.mess_limit'));
			}
		}

		$similar = Message::query()
			->whereBelongsTo($this->user)
			->where('date', '>', now()->subMinutes(5))
			->orderByDesc('date')
			->first();

		if ($similar && mb_strlen($similar->message) < 1000) {
			similar_text($message, $similar->message, $sim);

			if ($sim > 80) {
				throw new Exception(__('messages.mess_similar'));
			}
		}

		$message = Format::text($message);
		$message = preg_replace('/ +/', ' ', $message);
		$message = strtr($message, __('messages.stopwords'));

		$user->notify(new MessageNotification(null, MessageType::User, $this->user->username_formatted, $message));
	}

	public function delete(Request $request)
	{
		$items = Arr::wrap($request->post('id', []));
		$items = array_map('intval', $items);

		if (empty($items)) {
			throw new Exception('Не выбраны сообщения');
		}

		Message::query()
			->whereKey($items)
			->whereBelongsTo($this->user)
			->delete();
	}

	public function abuse(int $messageId)
	{
		$message = Message::query()
			->whereKey($messageId)
			->whereBelongsTo($this->user)
			->first();

		if (!$message) {
			throw new Exception('Сообщение не найдено');
		}

		$users = User::query()
			->select(['id'])
			->where('authlevel', '!=', 0)
			->get();

		foreach ($users as $user) {
			$user->notify(new MessageNotification(
				$this->user,
				MessageType::User,
				'<font color=red>' . $this->user->username . '</font>',
				'От кого: ' . $message->from . '<br>Дата отправления: ' . $message->date->format('d-m-Y H:i:s') . '<br>Текст сообщения: ' . $message->message
			));
		}
	}
}
