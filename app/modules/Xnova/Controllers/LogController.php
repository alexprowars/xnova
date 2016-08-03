<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\CombatReport;
use Xnova\Controller;

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

		$list = $this->db->extractResult($this->db->query("SELECT `id`, `user`, `title` FROM game_savelog WHERE `user` = '" . $this->user->id . "' "));

		$this->tag->setTitle('Логовница');
		$this->view->setVar('list', $list);
		$this->showTopPanel(false);
	}

	/**
	 * @Route("/delete/")
	 */
	public function deleteAction ()
	{
		if (!$this->auth->isAuthorized())
			$this->dispatcher->forward(['controller' => 'index', 'action' => 'index']);

		if ($this->request->hasQuery('id'))
		{
			$id = $this->request->getQuery('id', 'int', 0);

			if ($id > 0)
			{
				$raportrow = $this->db->query("SELECT * FROM game_savelog WHERE id = '" . $id . "'")->fetch();

				if ($this->user->id == $raportrow['user'])
				{
					$this->db->delete('game_savelog', 'id = ?', [$id]);

					$this->response->redirect("log/");
				}
				else
					$this->message("Ошибка удаления.", "Логовница", "/log/", 1);
			}
			else
				$this->message("Ошибка удаления.", "Логовница", "/log/", 1);
		}
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
			$message = "";

			if ($this->request->getPost('title', 'string', '') == '')
				$message = '<h1><font color=red>Введите название для боевого отчёта.</h1>';
			elseif ($this->request->getPost('code', 'string', '') == '')
				$message = '<h1><font color=red>Введите ID боевого отчёта.</h1>';
			else
			{
				$code = $this->request->getPost('code', 'string', '');

				$key = substr($code, 0, 32);
				$id = substr($code, 32, (mb_strlen($code, 'UTF-8') - 32));

				if (md5('xnovasuka' . $id) != $key)
					$this->message('Не правильный ключ', 'Ошибка', '', 0, false);
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

						$this->db->query("INSERT INTO game_savelog (`user`, `title`, `log`) VALUES ('" . $this->user->id . "', '" . addslashes(htmlspecialchars($_POST['title'])) . "', '" . addslashes($SaveLog) . "')");

						$message = 'Боевой отчёт успешно сохранён.';
					}
					else
						$message = 'Боевой отчёт не найден в базе';
				}
			}

			$this->message($message, "Логовница", "/log/", 2);
		}

		$this->tag->setTitle('Логовница');
		$this->showTopPanel(false);
	}

	/**
	 * @Route("/{id:[0-9]+}{params:(/.*)*}")
	 * @return bool
	 */
	public function infoAction ()
	{
		if ($this->request->hasQuery('id'))
		{
			$html = '';

			$raportrow = $this->db->query("SELECT * FROM game_savelog WHERE id = '" . $this->request->getQuery('id', 'int', 0) . "' ")->fetch();

			if (isset($raportrow['id']))
			{
				$result = json_decode($raportrow['log'], true);

				if (!$this->config->game->get('openRaportInNewWindow', 0) && $this->auth->isAuthorized())
				{
					if (!is_array($result) || ($raportrow['user'] == 0 && $result[0]['time'] > (time() - 7200)))
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
					$html = "<!DOCTYPE html><html><head><title>" . stripslashes($raportrow["title"]) . "</title>";
					$html .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"".$this->url->getBaseUri()."assets/css/bootstrap.css\">";
					$html .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"".$this->url->getBaseUri()."assets/css/style.css\">";
					$html .= "</head><body>";
					$html .= "<table width=\"99%\"><tr><td>";

					if (!is_array($result) || ($raportrow['user'] == 0 && $result[0]['time'] > (time() - 7200) && !$this->user->isAdmin()))
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
					$this->message('Запрашиваемого лога не существует в базе данных');
				else
				{
					$html = "<!DOCTYPE html><html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"".$this->url->getBaseUri()."assets/css/bootstrap.css\">";
					$html .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"".$this->url->getBaseUri()."assets/css/style.css\">";
					$html .= "</head><body><center>Запрашиваемого лога не существует в базе данных</center></body></html>";

					echo $html;

					$this->view->disable();
				}
			}
		}

		return true;
	}
}