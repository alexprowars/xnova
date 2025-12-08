<?php

namespace App\Http\Controllers;

use App\Engine\BattleReport;
use App\Exceptions\Exception;
use App\Exceptions\PageException;
use App\Models\LogsBattle;
use App\Models\Report;
use Illuminate\Http\Request;

class LogsController extends Controller
{
	public function index()
	{
		$logs = LogsBattle::query()->whereBelongsTo($this->user)
			->orderByDesc('id')->get();

		$items = [];

		foreach ($logs as $log) {
			$items[] = [
				'id' => $log->id,
				'title' => $log->title
			];
		}

		return $items;
	}

	public function delete(int $id)
	{
		$log = LogsBattle::query()->whereKey($id)
			->whereBelongsTo($this->user)
			->first();

		if (!$log) {
			throw new Exception('Боевой доклад не найден');
		}

		if (!$log->delete()) {
			throw new Exception('Ошибка удаления.');
		}
	}

	public function create(Request $request)
	{
		$title = $request->post('title');
		$code = $request->post('code');

		if (empty($title)) {
			throw new Exception('Введите название для боевого отчёта');
		} elseif (empty($code)) {
			throw new Exception('Введите ID боевого отчёта');
		}

		$key = substr($code, 0, 32);
		$id = (int) substr($code, 32, (mb_strlen($code, 'UTF-8') - 32));

		if (md5(config('app.key') . $id) != $key) {
			throw new Exception('Неправильный ключ');
		}

		$log = Report::find($id);

		if (!$log) {
			throw new Exception('Боевой отчёт не найден в базе');
		}

		if ($log->users_id[0] == $this->user->id && $log->no_contact == 1) {
			$dataLog = [null];
		} else {
			$dataLog = $log->data;

			foreach ($dataLog[0]['rw'] as $round => $data1) {
				unset($dataLog[0]['rw'][$round]['logA'], $dataLog[0]['rw'][$round]['logD']);
			}
		}

		$new = new LogsBattle();
		$new->user_id = $this->user->id;
		$new->title = addslashes(htmlspecialchars($title));
		$new->data = $dataLog;

		if (!$new->save()) {
			throw new Exception('Произошла ошибка при сохранении боевого отчета');
		}
	}

	public function info(int $id)
	{
		$raport = LogsBattle::find($id);

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

		return [
			'raport' => $report->report()['html']
		];
	}
}
