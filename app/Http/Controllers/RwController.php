<?php

namespace App\Http\Controllers;

use App\Engine\Battle\BattleReport;
use App\Exceptions\Exception;
use App\Exceptions\PageException;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class RwController extends Controller
{
	public function index(int $id, Request $request)
	{
		try {
			$signature = Crypt::decrypt($request->input('signature'));

			if ($signature != $id) {
				throw new Exception();
			}
		} catch (Throwable) {
			throw new PageException('Недействительная подпись');
		}

		$report = Report::find($id);

		if (!$report) {
			throw new PageException('Данный боевой отчет не найден или удалён');
		}

		if (!$this->user->isAdmin()) {
			if (!in_array($this->user->id, $report->users_id)) {
				throw new PageException('Вы не можете просматривать этот боевой доклад');
			}

			if ($report->users_id[0] == $this->user->id && $report->no_contact == 1) {
				throw new PageException('Контакт с вашим флотом потерян<br>(Ваш флот был уничтожен в первой волне атаки)');
			}
		}

		try {
			$html = BattleReport::fromArray($report->data)->report();
		} catch (Throwable) {
			throw new PageException('Ошибка обработки боевого отчета');
		}

		$logCode = md5(config('app.key') . $report->id) . $report->id;

		$html .= '<div class="text-center mt-2">ID боевого доклада: <a href="/logs/create?code=' . $logCode . '"><span style="color: red">' . $logCode . '</span></a></div>';

		return [
			'raport' => $html,
		];
	}
}
