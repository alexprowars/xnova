<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request;
use Nutnet\LaravelSms\SmsSender;
use App\Exceptions\ErrorException;
use App\Exceptions\Exception;
use App\Exceptions\RedirectException;
use App\Exceptions\SuccessException;
use App\Game;
use App\Helpers;
use App\Models\Support;
use App\User;
use App\Controller;

class SupportController extends Controller
{
	public function add()
	{
		$text = Request::post('text', '');
		$subject = Request::post('subject', '');

		if (empty($text) || empty($subject)) {
			throw new ErrorException('Не заполнены все поля');
		}

		$ticket = new Support();

		$ticket->user_id = $this->user->id;
		$ticket->subject = Helpers::checkString($subject);
		$ticket->text = Helpers::checkString($text);
		$ticket->status = 1;

		if (!$ticket->save()) {
			throw new Exception('Не удалось создать тикет');
		}

		app(SmsSender::class)
			->send(config('game.sms.login'), 'Создан новый тикет №' . $ticket->id . ' (' . $this->user->username . ')');

		throw new SuccessException('Задача добавлена');
	}

	public function answer($id)
	{
		$id = (int) $id;

		if (!$id) {
			throw new RedirectException('Не задан ID тикета', '/support/');
		}

		$text = Request::post('text', '');

		if (empty($text)) {
			throw new ErrorException('Не заполнены все поля');
		}

		$ticket = Support::query()->find($id);

		if (!$ticket) {
			throw new RedirectException('Тикет не найден', '/support/');
		}

		$text = $ticket->text . '<hr>' . $this->user->username . ' ответил в ' . date("d.m.Y H:i:s", time()) . ':<br>' . Helpers::checkString($text) . '';

		$ticket->text = $text;
		$ticket->status = 3;
		$ticket->update();

		User::sendMessage(1, false, time(), 4, $this->user->username, 'Поступил ответ на тикет №' . $id);

		if ($ticket->status == 2) {
			app(SmsSender::class)
				->send(config('game.sms.login'), 'Поступил ответ на тикет №' . $ticket->id . ' (' . $this->user->username . ')');
		}

		throw new SuccessException('Задача обновлена');
	}

	public function index()
	{
		$list = [];

		$tickets = Support::query()
			->where('user_id', $this->user->id)
			->orderByDesc('time')
			->get();

		foreach ($tickets as $ticket) {
			$list[] = [
				'id' => (int) $ticket->id,
				'status' => (int) $ticket->status,
				'subject' => $ticket->subject,
				'date' => Game::datezone("d.m.Y H:i:s", $ticket->time)
			];
		}

		return [
			'items' => $list
		];
	}

	public function info($id)
	{
		$ticket = Support::query()
			->where('user_id', $this->user->id)
			->where('id', $id)->first();

		if (!$ticket) {
			throw new \Exception('Тикет не найден');
		}

		return [
			'id' => (int) $ticket->id,
			'status' => (int) $ticket->status,
			'subject' => $ticket->subject,
			'date' => Game::datezone("d.m.Y H:i:s", $ticket->time),
			'text' => html_entity_decode($ticket->text, ENT_NOQUOTES, "CP1251"),
		];
	}
}
