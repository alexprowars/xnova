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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

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
			->select(['messages.id', 'type', 'time', 'text', 'from_id'])
			->orderBy('time', 'DESC');

		if ($category == 101) {
			$messages->addSelect(DB::raw('CONCAT(users.username, \' [\', users.galaxy,\':\', users.system,\':\', users.planet, \']\') as theme'))
				->leftJoin('users', 'users.id', '=', 'user_id')
				->where('from_id', $this->user->id);
		} else {
			$messages->addSelect('theme')->where('user_id', $this->user->id)
				->where('deleted', 0);

			if ($category < 100) {
				$messages->where('type', $category);
			}
		}

		$paginator = $messages->paginate($limit, null, null, $page);

		$items = $paginator->items();
		$parse['items'] = [];

		foreach ($items as $item) {
			if (preg_match_all('/href=\"\/(.*?)"/i', $item->text, $match)) {
				foreach ($match[1] as $rep) {
					$item->text = str_replace('/' . $rep, URL::to($rep), $item->text);
				}
			}

			if (preg_match('/#DATE\|(.*?)\|(.*?)#/i', $item->text, $match)) {
				$item->text = str_replace($match[0], Game::datezone(trim($match[1]), (int) $match[2]), $item->text);
			}

			$parse['items'][] = [
				'id' => $item->id,
				'type' => $item->type,
				'time' => $item->time?->utc()->toAtomString(),
				'from' => $item->from_id,
				'theme' => $item->theme ?? '',
				'text' => str_replace(["\r\n", "\n", "\r"], '<br>', stripslashes($item->text)),
			];
		}

		$parse['pagination'] = [
			'total' => $paginator->total(),
			'limit' => $limit,
			'page' => $paginator->currentPage()
		];

		$parse['parser'] = $this->user->getOption('bb_parser');

		return response()->state($parse);
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
			$mes = Message::query()
				->select(['id', 'text'])
				->where('id', $request->query('quote'))
				->where(function (Builder $query) {
					$query->where('user_id', $this->user->id)
						->orWhere('from_id', $this->user->id);
				})
				->first();

			if ($mes) {
				$page['message'] = '[quote]' . preg_replace('/<br(\s*)?\/?>/iu', "", $mes->text) . '[/quote]';
			}
		}

		return response()->state($page);
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
				->where('user_id', $this->user->id)
				->where('time', '>', now()->subMinute())
				->count();

			if ($lastSend > 0) {
				throw new Exception(__('messages.mess_limit'));
			}
		}

		$similar = Message::query()
			->where('user_id', $this->user->id)
			->where('time', '>', now()->subMinutes(5))
			->orderByDesc('time')
			->first();

		if ($similar && mb_strlen($similar->text) < 1000) {
			similar_text($message, $similar->text, $sim);

			if ($sim > 80) {
				throw new Exception(__('messages.mess_similar'));
			}
		}

		$from = $this->user->username . ' [' . $this->user->galaxy . ':' . $this->user->system . ':' . $this->user->planet . ']';

		$message = Format::text($message);
		$message = preg_replace('/ +/', ' ', $message);
		$message = strtr($message, __('messages.stopwords'));

		$user->notify(new MessageNotification(null, MessageType::User, $from, $message));
	}

	public function delete(Request $request)
	{
		$items = $request->post('id');

		if (!is_array($items) || !count($items)) {
			throw new Exception('Не выбраны сообщения');
		}

		$items = array_map('intval', $items);

		if (count($items)) {
			Message::query()
				->whereIn('id', $items)
				->where('user_id', $this->user->id)
				->update(['deleted' => 1]);
		}
	}

	public function abuse(int $messageId)
	{
		$mes = Message::query()
			->where('id', $messageId)
			->where('user_id', $this->user->id)
			->first();

		if (!$mes) {
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
				'От кого: ' . $mes->from . '<br>Дата отправления: ' . date('d-m-Y H:i:s', $mes->time) . '<br>Текст сообщения: ' . $mes->text
			));
		}
	}
}
