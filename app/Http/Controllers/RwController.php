<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace Xnova\Http\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Xnova\CombatReport;
use Xnova\Controller;
use Xnova\Exceptions\PageException;
use Xnova\Models\Report;

class RwController extends Controller
{
	public function index(int $id, string $key)
	{
		if (!$id) {
			throw new PageException('Боевой отчет не найден');
		}

		$report = Report::query()->find($id);

		if (!$report) {
			throw new PageException('Данный боевой отчет не найден или удалён');
		}

		$user_list = json_decode($report->id_users, true);

		if (!$this->user->isAdmin()) {
			if (md5(Config::get('app.key') . $report->id) != $key) {
				throw new PageException('Не правильный ключ');
			}

			if (!in_array($this->user->id, $user_list)) {
				throw new PageException('Вы не можете просматривать этот боевой доклад');
			}

			if ($user_list[0] == $this->user->id && $report->no_contact == 1) {
				throw new PageException('Контакт с вашим флотом потерян.<br>(Ваш флот был уничтожен в первой волне атаки.)');
			}
		}

		$result = json_decode($report->raport, true);
		$report = new CombatReport($result[0], $result[1], $result[2], $result[3], $result[4], $result[5]);

		$html = $report->report()['html'];
		$html .= "<div class='separator'></div><div class='text-center'>ID боевого доклада: <a href=\"" . URL::to('log/new/') . "?code=" . md5(Config::get('app.key') . $report->id) . $report->id . "/\"><font color=red>" . md5('xnovasuka' . $report->id) . $report->id . "</font></a></div>";

		$this->setTitle('Боевой доклад');
		$this->showTopPanel(false);

		return [
			'raport' => $html
		];
	}
}
