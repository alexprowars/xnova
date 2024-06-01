<?php

namespace App\Http\Controllers;

use App\Engine\BattleReport;
use App\Exceptions\ErrorException;
use App\Exceptions\PageException;
use App\Exceptions\RedirectException;
use App\Models\LogBattle;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class LogController extends Controller
{
	public function index()
	{
		if (!Auth::check()) {
			return redirect('/');
		}

		$logs = LogBattle::query()->where('user_id', $this->user->id)
			->orderByDesc('id')->get();

		$list = [];

		foreach ($logs as $log) {
			$list[] = [
				'id' => (int) $log->id,
				'title' => $log->title
			];
		}

		return response()->state([
			'items' => $list
		]);
	}

	public function deleteAction()
	{
		if (!Auth::check()) {
			throw new PageException('Доступ запрещен');
		}

		if (!Request::has('id')) {
			throw new RedirectException('/log', 'Ошибка удаления.');
		}

		$id = (int) Request::query('id', 0);

		if (!$id) {
			throw new RedirectException('/log', 'Ошибка удаления.');
		}

		$log = LogBattle::where('id', $id)
			->where('user_id', $this->user->id)
			->first();

		if (!$log) {
			throw new RedirectException('/log', 'Ошибка удаления.');
		}

		if (!$log->delete()) {
			throw new RedirectException('/log', 'Ошибка удаления.');
		}

		throw new RedirectException('/log', 'Отчет удалён');
	}

	public function new()
	{
		$title = Request::post('title', '');

		if ($title == '') {
			throw new RedirectException('/log', '<h1><font color=red>Введите название для боевого отчёта.</h1>');
		} elseif (Request::post('code', '') == '') {
			throw new RedirectException('/log', '<h1><font color=red>Введите ID боевого отчёта.</h1>');
		}

		$code = Request::post('code', '');

		$key = substr($code, 0, 32);
		$id = (int) substr($code, 32, (mb_strlen($code, 'UTF-8') - 32));

		if (md5(config('app.key') . $id) != $key) {
			throw new RedirectException('/log', 'Неправильный ключ');
		}

		$log = Report::find($id);

		if (!$log) {
			throw new RedirectException('/log', 'Боевой отчёт не найден в базе');
		}

		if ($log->users_id[0] == $this->user->id && $log->no_contact == 1) {
			$SaveLog = [null];
		} else {
			$SaveLog = $log->data;

			foreach ($SaveLog[0]['rw'] as $round => $data1) {
				unset($SaveLog[0]['rw'][$round]['logA'], $SaveLog[0]['rw'][$round]['logD']);
			}
		}

		$new = new LogBattle();
		$new->user_id = $this->user->id;
		$new->title = addslashes(htmlspecialchars($title));
		$new->data = $SaveLog;

		if (!$new->save()) {
			throw new ErrorException('Произошла ошибка при сохранении боевого отчета');
		}

		throw new RedirectException('/log', 'Боевой отчёт успешно сохранён.');
	}

	public function info(int $id)
	{
		$raport = LogBattle::find($id);

		if (!$raport) {
			throw new PageException('Запрашиваемого лога не существует в базе данных');
		}

		if ($raport->data[0] === null) {
			throw new PageException('Контакт с флотом потерян.<br>(Флот был уничтожен в первой волне атаки.)');
		}

		if (($raport->user_id == 0 && $raport->data[0]['time'] > (time() - 7200) && !$this->user->isAdmin())) {
			throw new PageException('Данный лог боя пока недоступен для просмотра!');
		}

		$report = new BattleReport($raport->data[0], $raport->data[1], $raport->data[2], $raport->data[3], $raport->data[4], $raport->data[5], $raport->data[6]);

		return response()->state([
			'raport' => $report->report()['html']
		]);
	}
}
