<?php

namespace App\Http\Controllers;

use App\Engine\Battle\BattleReport;
use App\Exceptions\Exception;
use App\Exceptions\PageException;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;
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
			throw new PageException(__('logs.invalid_signature'));
		}

		$report = Report::find($id);

		if (!$report) {
			throw new PageException(__('logs.report_missing_or_deleted'));
		}

		if (!$this->user->isAdmin()) {
			if (!in_array($this->user->id, $report->users_id)) {
				throw new PageException(__('logs.view_forbidden'));
			}

			if ($report->hasLostContact($this->user->id)) {
				throw new PageException(__('logs.own_fleet_contact_lost'));
			}
		}

		try {
			$html = new BattleReport($report->data)->report();
		} catch (Throwable) {
			throw new PageException(__('logs.processing_failed'));
		}

		$logCode = md5(config('app.key') . $report->id) . $report->id;

		$html .= '<div class="text-center mt-2">' . __('logs.report_id_label') . ' <a href="/logs/create?code=' . $logCode . '"><span style="color: red">' . $logCode . '</span></a></div>';

		return Inertia::render('Rw', [
			'raport' => $html,
		]);
	}
}
