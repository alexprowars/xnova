<?php

namespace App\Http\Controllers;

use App\Engine\Enums\MessageType;
use App\Exceptions\Exception;
use App\Exceptions\RedirectException;
use App\Exceptions\SuccessException;
use App\Helpers;
use App\Models\Support;
use App\Models\User;
use Illuminate\Support\Facades\Request;
use Nutnet\LaravelSms\SmsSender;

class SupportController extends Controller
{
	public function add()
	{
		$text = Request::post('text', '');
		$subject = Request::post('subject', '');

		if (empty($text) || empty($subject)) {
			throw new Exception('Не заполнены все поля');
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
			throw new RedirectException('/support', 'Не задан ID тикета');
		}

		$text = Request::post('text', '');

		if (empty($text)) {
			throw new Exception('Не заполнены все поля');
		}

		$ticket = Support::query()->find($id);

		if (!$ticket) {
			throw new RedirectException('/support', 'Тикет не найден');
		}

		$text = $ticket->text . '<hr>' . $this->user->username . ' ответил в ' . date("d.m.Y H:i:s", time()) . ':<br>' . Helpers::checkString($text) . '';

		$ticket->text = $text;
		$ticket->status = 3;
		$ticket->update();

		User::sendMessage(1, null, now(), MessageType::System, $this->user->username, 'Поступил ответ на тикет №' . $id);

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
				'date' => $ticket->time?->utc()->toAtomString(),
			];
		}

		return response()->state([
			'items' => $list
		]);
	}

	public function info($id)
	{
		$ticket = Support::query()
			->where('user_id', $this->user->id)
			->where('id', $id)->first();

		if (!$ticket) {
			throw new \Exception('Тикет не найден');
		}

		return response()->state([
			'id' => (int) $ticket->id,
			'status' => (int) $ticket->status,
			'subject' => $ticket->subject,
			'date' => $ticket->time?->utc()->toAtomString(),
			'text' => $ticket->text,
		]);
	}
}
