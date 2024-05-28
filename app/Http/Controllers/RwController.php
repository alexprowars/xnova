<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\URL;
use App\CombatReport;
use App\Controller;
use App\Exceptions\PageException;
use App\Models\Report;

class RwController extends Controller
{
	public function index(int $id, string $key)
	{
		if (!$id) {
			throw new PageException('Боевой отчет не найден');
		}

		$report = Report::find($id);

		if (!$report) {
			throw new PageException('Данный боевой отчет не найден или удалён');
		}

		if (!$this->user->isAdmin()) {
			if (md5(config('app.key') . $report->id) != $key) {
				throw new PageException('Не правильный ключ');
			}

			if (!in_array($this->user->id, $report->users_id)) {
				throw new PageException('Вы не можете просматривать этот боевой доклад');
			}

			if ($report->users_id[0] == $this->user->id && $report->no_contact == 1) {
				throw new PageException('Контакт с вашим флотом потерян.<br>(Ваш флот был уничтожен в первой волне атаки.)');
			}
		}

		$combatReport = new CombatReport($report->data[0], $report->data[1], $report->data[2], $report->data[3], $report->data[4], $report->data[5]);

		$html = $combatReport->report()['html'];
		$html .= "<div class='separator'></div><div class='text-center'>ID боевого доклада: <a href=\"" . URL::to('log/new/') . "?code=" . md5(config('app.key') . $report->id) . $report->id . "/\"><font color=red>" . md5('xnovasuka' . $report->id) . $report->id . "</font></a></div>";

		return response()->state([
			'raport' => $html
		]);
	}
}
