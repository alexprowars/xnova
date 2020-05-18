<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace Xnova\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Xnova\CombatReport;
use Xnova\Controller;
use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\PageException;
use Xnova\Exceptions\RedirectException;
use Xnova\Models\LogBattle;
use Xnova\Models\Report;

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

		$this->setTitle('Логовница');
		$this->showTopPanel(false);

		return [
			'items' => $list
		];
	}

	/**
	 * @Route("/delete{params:(/.*)*}")
	 */
	public function deleteAction()
	{
		if (!Auth::check()) {
			throw new PageException('Доступ запрещен');
		}

		if (!Request::has('id')) {
			throw new RedirectException('Ошибка удаления.', '/log/');
		}

		$id = (int) Request::query('id', 0);

		if (!$id) {
			throw new RedirectException('Ошибка удаления.', '/log/');
		}

		$log = LogBattle::findFirst([
			'conditions' => 'id = ?0 AND user_id = ?1',
			'bind' => [$id, $this->user->id],
		]);

		if (!$log) {
			throw new RedirectException('Ошибка удаления.', '/log/');
		}

		if (!$log->delete()) {
			throw new RedirectException('Ошибка удаления.', '/log/');
		}

		throw new RedirectException('Отчет удалён', '/log/');
	}

	public function new()
	{
		if (!Auth::check()) {
			return redirect('/');
		}

		$this->setTitle('Логовница');
		$this->showTopPanel(false);

		return [];
	}

	public function newSave()
	{
		$title = Request::post('title', '');

		if ($title == '') {
			throw new RedirectException('<h1><font color=red>Введите название для боевого отчёта.</h1>', '/log/');
		} elseif (Request::post('code', '') == '') {
			throw new RedirectException('<h1><font color=red>Введите ID боевого отчёта.</h1>', '/log/');
		}

		$code = Request::post('code', '');

		$key = substr($code, 0, 32);
		$id = (int) substr($code, 32, (mb_strlen($code, 'UTF-8') - 32));

		if (md5(config('app.key') . $id) != $key) {
			throw new RedirectException('Неправильный ключ', '/log/');
		}

		$log = Report::query()->find($id);

		if (!$log) {
			throw new RedirectException('Боевой отчёт не найден в базе', '/log/');
		}

		$user_list = json_decode($log->id_users);

		if ($user_list[0] == $this->user->id && $log->no_contact == 1) {
			$SaveLog = "Контакт с флотом потерян.<br>(Флот был уничтожен в первой волне атаки.)";
		} else {
			$SaveLog = json_decode($log->raport, true);

			foreach ($SaveLog[0]['rw'] as $round => $data1) {
				unset($SaveLog[0]['rw'][$round]['logA']);
				unset($SaveLog[0]['rw'][$round]['logD']);
			}

			$SaveLog = json_encode($SaveLog);
		}

		$new = new LogBattle();

		$new->user_id = $this->user->id;
		$new->title = addslashes(htmlspecialchars($title));
		$new->log = $SaveLog;

		if (!$new->save()) {
			throw new ErrorException('Произошла ошибка при сохранении боевого отчета');
		}

		throw new RedirectException('Боевой отчёт успешно сохранён.', '/log/');
	}

	public function info($id)
	{
		$id = (int) $id;

		$raportrow = LogBattle::query()->find($id);

		if (!$raportrow) {
			throw new PageException('Запрашиваемого лога не существует в базе данных');
		}

		$result = json_decode($raportrow->log, true);

		if (!is_array($result) || ($raportrow->user_id == 0 && $result[0]['time'] > (time() - 7200) && !$this->user->isAdmin())) {
			throw new PageException('Данный лог боя пока недоступен для просмотра!');
		}

		$report = new CombatReport($result[0], $result[1], $result[2], $result[3], $result[4], $result[5], $result[6]);

		$this->setTitle('Боевой доклад');
		$this->showTopPanel(false);

		return [
			'raport' => $report->report()['html']
		];
	}
}
