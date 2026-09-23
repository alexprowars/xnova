<?php

namespace App\Http\Controllers;

use App\Engine\Battle\BattleReport;
use App\Exceptions\Exception;
use App\Exceptions\PageException;
use App\Models\LogsBattle;
use App\Models\Report;
use App\Support\ToastType;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Throwable;

class LogsController extends Controller
{
	public function index()
	{
		$logs = LogsBattle::query()->whereBelongsTo($this->user)
			->orderByDesc('id')->get();

		$items = [];

		foreach ($logs as $log) {
			$items[] = [
				'id' => $log->id,
				'title' => $log->title,
			];
		}

		return Inertia::render('Logs/List', [
			'items' => $items,
		]);
	}

	public function create()
	{
		return Inertia::render('Logs/Create');
	}

	public function delete(int $id): void
	{
		$log = LogsBattle::query()->whereKey($id)
			->whereBelongsTo($this->user)
			->first();

		if (!$log) {
			throw new Exception(__('logs.battle_report_not_found'));
		}

		if (!$log->delete()) {
			throw new Exception(__('logs.delete_failed'));
		}
	}

	public function store(Request $request)
	{
		$title = $request->post('title');
		$code = $request->post('code');

		if (empty($title)) {
			throw new PageException(__('logs.title_required'));
		}

		if (empty($code)) {
			throw new PageException(__('logs.report_id_required'));
		}

		$key = substr($code, 0, 32);
		$id = (int) substr($code, 32, (mb_strlen($code) - 32));

		if (md5(config('app.key') . $id) != $key) {
			throw new PageException(__('logs.invalid_key'));
		}

		$log = Report::find($id);

		if (!$log) {
			throw new PageException(__('logs.report_not_found'));
		}

		if ($log->hasLostContact($this->user->id)) {
			$dataLog = [];
		} else {
			$dataLog = $log->data;
		}

		$new = new LogsBattle();
		$new->user()->associate($this->user);
		$new->title = addslashes(htmlspecialchars($title));
		$new->data = $dataLog;

		if (!$new->save()) {
			throw new PageException(__('logs.save_failed'));
		}

		toast(ToastType::SUCCESS, __('logs.saved'));

		return to_route('logs');
	}

	public function detail(int $id)
	{
		$raport = LogsBattle::find($id);

		if (!$raport) {
			throw new PageException(__('logs.log_not_found'));
		}

		if (empty($raport->data)) {
			throw new PageException(__('logs.fleet_contact_lost'));
		}

		if (!$raport->user_id && Carbon::parse($raport->data['date'])->isAfter(now()->subHours(2)) && !$this->user?->isAdmin()) {
			throw new PageException(__('logs.not_available_yet'));
		}

		try {
			$html = new BattleReport($raport->data)->report();
		} catch (Throwable) {
			throw new PageException(__('logs.processing_failed'));
		}

		return Inertia::render('Logs/Log', [
			'raport' => $html,
		]);
	}
}
