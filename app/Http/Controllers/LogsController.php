<?php

namespace App\Http\Controllers;

use App\Engine\Battle\BattleReport;
use App\Exceptions\Exception;
use App\Exceptions\PageException;
use App\Models\LogsBattle;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Throwable;

class LogsController extends Controller
{
	public function index(): array
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

	public function delete(int $id): void
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

	public function create(Request $request): void
	{
		$title = $request->post('title');
		$code = $request->post('code');

		if (empty($title)) {
			throw new Exception('Введите название для боевого отчёта');
		}

		if (empty($code)) {
			throw new Exception('Введите ID боевого отчёта');
		}

		$key = substr($code, 0, 32);
		$id = (int) substr($code, 32, (mb_strlen($code) - 32));

		if (md5(config('app.key') . $id) != $key) {
			throw new Exception('Неправильный ключ');
		}

		$log = Report::find($id);

		if (!$log) {
			throw new Exception('Боевой отчёт не найден в базе');
		}

		if ($log->users_id[0] == $this->user->id && $log->no_contact) {
			$dataLog = [null];
		} else {
			$dataLog = $log->data;

			foreach ($dataLog['result']['rw'] as $round => $data1) {
				unset($dataLog['result']['rw'][$round]['logA'], $dataLog['result']['rw'][$round]['logD']);
			}
		}

		$new = new LogsBattle();
		$new->user()->associate($this->user);
		$new->title = addslashes(htmlspecialchars($title));
		$new->data = $dataLog;

		if (!$new->save()) {
			throw new Exception('Произошла ошибка при сохранении боевого отчета');
		}
	}

	public function info(int $id): array
	{
		$raport = LogsBattle::find($id);

		if (!$raport) {
			throw new PageException('Запрашиваемого лога не существует в базе данных');
		}

		if ($raport->data['result'] === null) {
			throw new PageException('Контакт с флотом потерян.<br>(Флот был уничтожен в первой волне атаки.)');
		}

		if (!$raport->user_id && Carbon::parse($raport->data['result']['date'])->isAfter(now()->subHours(2)) && !$this->user->isAdmin()) {
			throw new PageException('Данный лог боя пока недоступен для просмотра!');
		}

		try {
			$html = new BattleReport($raport->data)->report();
		} catch (Throwable) {
			throw new PageException('Ошибка обработки боевого отчета');
		}

		return [
			'raport' => $html,
		];
	}
}
