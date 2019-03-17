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
use Xnova\Exceptions\PageException;
use Xnova\Exceptions\RedirectException;
use Xnova\Models\Rw;
use Xnova\Request;

/**
 * @RoutePrefix("/rw")
 * @Route("/")
 * @Private
 */
class RwController extends Controller
{
	public function initialize ()
	{
		parent::initialize();

		if ($this->dispatcher->wasForwarded())
			return;
	}

	/**
	 * @Route("/{id:[0-9]+}/{k:[a-z0-9]+}{params:(/.*)*}")
	 * @param $id
	 * @param $key
	 * @throws PageException
	 */
	public function indexAction ($id, $key)
	{
		if (!$this->request->hasQuery('id'))
			throw new PageException('Боевой отчет не найден');

		$raportrow = Rw::findFirst((int) $id);

		if (!$raportrow)
			throw new PageException('Данный боевой отчет не найден или удалён');

		$user_list = json_decode($raportrow->id_users, true);

		if (!$this->user->isAdmin())
		{
			if (md5($this->config->application->encryptKey.$raportrow->id) != $key)
				throw new PageException('Не правильный ключ');

			if (!in_array($this->user->id, $user_list))
				throw new PageException('Вы не можете просматривать этот боевой доклад');

			if ($user_list[0] == $this->user->id && $raportrow->no_contact == 1)
				throw new PageException('Контакт с вашим флотом потерян.<br>(Ваш флот был уничтожен в первой волне атаки.)');
		}

		$result = json_decode($raportrow->raport, true);
		$report = new CombatReport($result[0], $result[1], $result[2], $result[3], $result[4], $result[5]);

		$html = $report->report()['html'];
		$html .= "<div class='separator'></div><div class='text-center'>ID боевого доклада: <a href=\"".$this->url->get('log/new/')."?code=" . md5($this->config->application->encryptKey.$raportrow->id) . $raportrow->id . "/\"><font color=red>" . md5('xnovasuka' . $raportrow->id) . $raportrow->id . "</font></a></div>";

		Request::addData('page', [
			'raport' => $html
		]);

		$this->tag->setTitle('Боевой доклад');
		$this->showTopPanel(false);
	}
}