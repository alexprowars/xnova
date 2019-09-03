<?php

namespace Xnova\Http\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Xnova\CombatReport;
use Xnova\Controller;
use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\PageException;
use Xnova\Exceptions\RedirectException;
use Xnova\Models\BattleLog;

/**
 * @RoutePrefix("/log")
 * @Route("/")
 * @Private
 */
class LogController extends Controller
{
	public function index ()
	{
		if (!Auth::check())
			$this->dispatcher->forward(['controller' => 'index', 'action' => 'index']);

		$logs = BattleLog::find([
			'conditions' => 'user_id = ?0',
			'bind' => [$this->user->id],
			'order' => 'id DESC'
		]);

		$list = [];

		foreach ($logs as $log)
		{
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
	public function deleteAction ()
	{
		if (!Auth::check())
			throw new PageException('Доступ запрещен');

		if (!Request::has('id'))
			throw new RedirectException('Ошибка удаления.', '/log/');

		$id = (int) Request::query('id', 0);

		if (!$id)
			throw new RedirectException('Ошибка удаления.', '/log/');

		$log = BattleLog::findFirst([
			'conditions' => 'id = ?0 AND user_id = ?1',
			'bind' => [$id, $this->user->id],
		]);

		if (!$log)
			throw new RedirectException('Ошибка удаления.', '/log/');

		if (!$log->delete())
			throw new RedirectException('Ошибка удаления.', '/log/');

		throw new RedirectException('Отчет удалён', '/log/');
	}

	/**
	 * @Route("/new/")
	 */
	public function newAction ()
	{
		if (!Auth::check())
			$this->dispatcher->forward(['controller' => 'index', 'action' => 'index']);

		if (Request::instance()->isMethod('post'))
		{
			$title = Request::post('title', '');

			if ($title == '')
				$message = '<h1><font color=red>Введите название для боевого отчёта.</h1>';
			elseif (Request::post('code', '') == '')
				$message = '<h1><font color=red>Введите ID боевого отчёта.</h1>';
			else
			{
				$code = Request::post('code', '');

				$key = substr($code, 0, 32);
				$id = (int) substr($code, 32, (mb_strlen($code, 'UTF-8') - 32));

				if (md5(Config::get('app.key').$id) != $key)
					throw new RedirectException('Не правильный ключ', '');
				else
				{
					$log = $this->db->query("SELECT * FROM rw WHERE `id` = '" . $id . "'")->fetch();

					if (isset($log['id']))
					{
						$user_list = json_decode($log['id_users']);

						if ($user_list[0] == $this->user->id && $log['no_contact'] == 1)
							$SaveLog = "Контакт с флотом потерян.<br>(Флот был уничтожен в первой волне атаки.)";
						else
						{
							$SaveLog = json_decode($log['raport'], true);

							foreach ($SaveLog[0]['rw'] as $round => $data1)
							{
								unset($SaveLog[0]['rw'][$round]['logA']);
								unset($SaveLog[0]['rw'][$round]['logD']);
							}

							$SaveLog = json_encode($SaveLog);
						}

						$log = new BattleLog();

						$log->user_id = $this->user->id;
						$log->title = addslashes(htmlspecialchars($title));
						$log->log = $SaveLog;

						if (!$log->save())
							throw new ErrorException('Произошла ошибка при сохранении боевого отчета');

						$message = 'Боевой отчёт успешно сохранён.';
					}
					else
						$message = 'Боевой отчёт не найден в базе';
				}
			}

			throw new RedirectException($message, '/log/');
		}

		$this->setTitle('Логовница');
		$this->showTopPanel(false);
	}

	/**
	 * @Route("/{id:[0-9]+}{params:(/.*)*}")
	 * @param $id
	 * @throws PageException
	 */
	public function infoAction ($id)
	{
		$id = (int) $id;

		$raportrow = BattleLog::findFirst($id);

		if (!$raportrow)
			throw new PageException('Запрашиваемого лога не существует в базе данных');

		$result = json_decode($raportrow->log, true);

		if (!is_array($result) || ($raportrow->user_id == 0 && $result[0]['time'] > (time() - 7200) && !$this->user->isAdmin()))
			throw new PageException('Данный лог боя пока недоступен для просмотра!');

		$report = new CombatReport($result[0], $result[1], $result[2], $result[3], $result[4], $result[5], $result[6]);

		$this->setTitle('Боевой доклад');
		$this->showTopPanel(false);

		return [
			'raport' => $report->report()['html']
		];
	}
}