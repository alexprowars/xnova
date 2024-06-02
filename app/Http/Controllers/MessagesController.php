<?php

namespace App\Http\Controllers;

use App\Engine\Game;
use App\Exceptions\Exception;
use App\Exceptions\SuccessException;
use App\Format;
use App\Models;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class MessagesController extends Controller
{
	public function write(Request $request, $userId)
	{
		if (!$userId) {
			throw new Exception(__('messages.mess_no_ownerid'));
		}

		$userId = (int) $userId;

		$OwnerRecord = User::find($userId);

		if (!$OwnerRecord) {
			throw new Exception(__('messages.mess_no_owner'));
		}

		if ($request->post('text')) {
			$text = $request->post('text', '');

			$error = 0;

			if ($text == '') {
				throw new Exception(__('messages.mess_no_text'));
			}

			if (!$error && $this->user->message_block > time()) {
				throw new Exception(__('messages.mess_similar'));
			}

			if ($this->user->lvl_minier == 1 && $this->user->lvl_raid) {
				if ($this->user->created_at->getTimestamp() > time() - 86400) {
					$lastSend = Message::query()
						->where('user_id', $this->user->id)
						->where('time', time() - (1 * 60))
						->count();

					if ($lastSend > 0) {
						throw new Exception(__('messages.mess_limit'));
					}
				}
			}

			$similar = Message::query()->where('user_id', $this->user->id)
				->where('time', now()->subMinutes(5))
				->orderByDesc('time')
				->first();

			if ($similar && mb_strlen($similar->text) < 1000) {
				similar_text($text, $similar->text, $sim);

				if ($sim > 80) {
					throw new Exception(__('messages.mess_similar'));
				}
			}

			$From = $this->user->username . " [" . $this->user->galaxy . ":" . $this->user->system . ":" . $this->user->planet . "]";

			$Message = Format::text($text);
			$Message = preg_replace('/[ ]+/', ' ', $Message);
			$Message = strtr($Message, __('messages.stopwords'));

			User::sendMessage($OwnerRecord['id'], false, 0, 2, $From, $Message);

			throw new SuccessException(__('messages.mess_sended'));
		}

		$page = [
			'text' => '',
			'id' => $OwnerRecord['id'],
			'to' => $OwnerRecord['username'] . " [" . $OwnerRecord['galaxy'] . ":" . $OwnerRecord['system'] . ":" . $OwnerRecord['planet'] . "]"
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
				$page['text'] = '[quote]' . preg_replace('/<br(\s*)?\/?>/iu', "", $mes->text) . '[/quote]';
			}
		}

		return response()->state($page);
	}

	public function delete(Request $request)
	{
		$items = $request->post('delete');

		if (!is_array($items) || !count($items)) {
			return false;
		}

		$items = array_map('intval', $items);

		if (count($items)) {
			Message::query()
				->whereIn('id', $items)
				->where('user_id', $this->user->id)
				->update(['deleted' => 1]);
		}

		return true;
	}

	public function abuse($messageId)
	{
		$mes = Message::query()
			->where('id', (int) $messageId)
			->where('user_id', $this->user->id)
			->first();

		if (!$mes) {
			throw new Exception('Сообщение не найдено');
		}

		$users = Models\User::query()
			->select(['id'])
			->where('authlevel', '!=', 0)
			->get();

		foreach ($users as $user) {
			User::sendMessage(
				$user->id,
				$this->user->id,
				0,
				1,
				'<font color=red>' . $this->user->username . '</font>',
				'От кого: ' . $mes->from . '<br>Дата отправления: ' . date("d-m-Y H:i:s", $mes->time) . '<br>Текст сообщения: ' . $mes->text
			);
		}

		throw new SuccessException('Жалоба отправлена администрации игры');
	}

	public function index(Request $request)
	{
		$parse = [];

		$types = [1, 2, 3, 4, 5, 6, 15, 99, 100, 101];
		$limits = [5, 10, 25, 50, 100, 200];

		$category = 100;

		if (Session::has('m_cat')) {
			$category = (int) Session::get('m_cat');
		}

		if ($request->post('category')) {
			$category = (int) $request->post('category', 100);
		}

		if (!in_array($category, $types)) {
			$category = 100;
		}

		$limit = 10;

		if (Session::has('m_limit')) {
			$limit = (int) Session::get('m_limit');
		}

		if ($request->post('limit')) {
			$limit = (int) $request->post('limit', 10);
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

		if ($request->post('delete')) {
			$this->delete($request);
		}

		$parse['limit'] = $limit;
		$parse['category'] = $category;

		if ($this->user->messages > 0) {
			$this->user->messages = 0;
			$this->user->update();
		}

		$messages = Message::query()
			->select(['id', 'type', 'time', 'text', 'from_id'])
			->orderBy('time', 'DESC');

		if ($category == 101) {
			$messages->addSelect(DB::raw('CONCAT(users.username, \' [\', users.galaxy,\':\', users.system,\':\',users.planet, \']\') as theme'))
				->join('users', 'users.id', '=', 'user_id')
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
				'id' => (int) $item->id,
				'type' => (int) $item->type,
				'time' => $item->time?->utc()->toAtomString(),
				'from' => (int) $item->from_id,
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
}
