<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\CombatReport;
use Xnova\Controller;
use Xnova\Exceptions\ErrorException;
use Xnova\Exceptions\RedirectException;
use Xnova\Models\BattleLog;
use Xnova\Request;

/**
 * @RoutePrefix("/log")
 * @Route("/")
 * @Private
 */
class LogController extends Controller
{
	public function indexAction ()
	{
		if (!$this->auth->isAuthorized())
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

		Request::addData('page', [
			'items' => $list
		]);

		$this->tag->setTitle('Логовница');
		$this->showTopPanel(false);
	}

	/**
	 * @Route("/delete{params:(/.*)*}")
	 */
	public function deleteAction ()
	{
		if (!$this->auth->isAuthorized())
			$this->dispatcher->forward(['controller' => 'index', 'action' => 'index']);

		if (!$this->request->hasQuery('id'))
			throw new RedirectException("Ошибка удаления.", "Логовница", "/log/", 2);

		$id = (int) $this->request->getQuery('id', 'int', 0);

		if (!$id)
			throw new RedirectException("Ошибка удаления.", "Логовница", "/log/", 2);

		$log = BattleLog::findFirst([
			'conditions' => 'id = ?0 AND user_id = ?1',
			'bind' => [$id, $this->user->id],
		]);

		if (!$log)
			throw new RedirectException("Ошибка удаления.", "Логовница", "/log/", 2);

		if (!$log->delete())
			throw new RedirectException("Ошибка удаления.", "Логовница", "/log/", 2);

		$this->response->redirect("log/");
	}

	/**
	 * @Route("/new/")
	 */
	public function newAction ()
	{
		if (!$this->auth->isAuthorized())
			$this->dispatcher->forward(['controller' => 'index', 'action' => 'index']);

		if ($this->request->isPost())
		{
			$title = $this->request->getPost('title', 'string', '');

			if ($title == '')
				$message = '<h1><font color=red>Введите название для боевого отчёта.</h1>';
			elseif ($this->request->getPost('code', 'string', '') == '')
				$message = '<h1><font color=red>Введите ID боевого отчёта.</h1>';
			else
			{
				$code = $this->request->getPost('code', 'string', '');

				$key = substr($code, 0, 32);
				$id = (int) substr($code, 32, (mb_strlen($code, 'UTF-8') - 32));

				if (md5($this->config->application->encryptKey.$id) != $key)
					throw new RedirectException('Не правильный ключ', 'Ошибка', '', 0);
				else
				{
					$log = $this->db->query("SELECT * FROM game_rw WHERE `id` = '" . $id . "'")->fetch();

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

						if (!$log->create())
							throw new ErrorException('Произошла ошибка при сохранении боевого отчета');

						$message = 'Боевой отчёт успешно сохранён.';
					}
					else
						$message = 'Боевой отчёт не найден в базе';
				}
			}

			throw new RedirectException($message, "Логовница", "/log/", 2);
		}

		$this->tag->setTitle('Логовница');
		$this->showTopPanel(false);
	}

	/**
	 * @Route("/{id:[0-9]+}{params:(/.*)*}")
	 */
	public function infoAction ()
	{
		if ($this->request->hasQuery('id'))
		{
			$html = '';

			$id = (int) $this->request->getQuery('id', 'int', 0);

			$raportrow = BattleLog::findFirst($id);

			if ($raportrow)
			{
				$result = json_decode($raportrow->log, true);

				if (!$this->config->game->get('openRaportInNewWindow', 0) && $this->auth->isAuthorized())
				{
					if (!is_array($result) || ($raportrow->user_id == 0 && $result[0]['time'] > (time() - 7200)))
						$html .= "<center>Данный лог боя пока недоступен для просмотра!</center>";
					else
					{
						$report = new CombatReport($result[0], $result[1], $result[2], $result[3], $result[4], $result[5], $result[6]);

						$html .= $report->report()['html'];
					}

					$this->tag->setTitle('Боевой доклад');
					$this->view->setVar('html', $html);
					$this->showTopPanel(false);
				}
				else
				{
					$html = "<!DOCTYPE html><html><head><title>" . stripslashes($raportrow->title) . "</title>";
					$html .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"".$this->url->getStatic('assets/css/bootstrap.css')."\">";
					$html .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"".$this->url->getStatic('assets/css/style.css')."\">";
					$html .= "</head><body>";
					$html .= "<table width=\"99%\"><tr><td>";

					if (!is_array($result) || ($raportrow->user_id == 0 && $result[0]['time'] > (time() - 7200) && !$this->user->isAdmin()))
						$html .= "<center>Данный лог боя пока недоступен для просмотра!</center>";
					else
					{
						$report = new CombatReport($result[0], $result[1], $result[2], $result[3], $result[4], $result[5], $result[6]);

						$html .= $report->report()['html'];
					}

					$html .= "</td></tr></table>";
					$html .= $this->view->partial('shared/counters');
					$html .= "</body></html>";

					echo $html;

					$this->view->disable();
				}
			}
			else
			{
				if (!$this->config->game->get('openRaportInNewWindow', 0) && $this->auth->isAuthorized())
					throw new ErrorException('Запрашиваемого лога не существует в базе данных');
				else
				{
					$html = '<!DOCTYPE html><html><head><link rel="stylesheet" type="text/css" href="'.$this->url->getStatic('assets/css/bootstrap.css').'">';
					$html .= '<link rel="stylesheet" type="text/css" href="'.$this->url->getBaseUri()."assets/css/style.css\">";
					$html .= '</head><body><center>Запрашиваемого лога не существует в базе данных</center></body></html>';

					echo $html;

					$this->view->disable();
				}
			}
		}
	}
}