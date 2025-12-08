<?php

namespace App\Http\Controllers;

use App\Engine\BattleReport;
use App\Engine\Enums\ItemType;
use App\Engine\Fleet\CombatEngine\Simulation;
use App\Exceptions\Exception;
use App\Facades\Vars;
use App\Models\LogsSimulation;
use Illuminate\Http\Request;

class SimController extends Controller
{
	public function index($data = '')
	{
		if (!empty($data)) {
			$units = explode(';', $data);
		} elseif (request()->has('units')) {
			$units = explode(';', request()->get('units'));
		} else {
			$units = [];
		}

		$units = array_filter($units);

		$maxSlots = config('game.maxSlotsInSim', 5);

		$parse = [];
		$parse['slots'] = [
			'max' => $maxSlots,
			'attackers' => [
				0 => [],
			],
			'defenders' => [
				0 => [],
			],
		];

		$parse['tech'] = [109, 110, 111, 120, 121, 122];

		foreach ($units as $row) {
			$element = explode(',', $row);

			if (!is_numeric($element[0])) {
				$element[0] = Vars::getIdByName($element[0]);
			}

			if (isset($element[1])) {
				$parse['slots']['defenders'][0][$element[0]] = ['c' => $element[1]];
			}
		}

		$res = Vars::getItemsByType([ItemType::FLEET, ItemType::DEFENSE, ItemType::TECH]);

		foreach ($res as $id) {
			if ($this->planet->getLevel($id) > 0) {
				$parse['slots']['attackers'][0][$id] = ['c' => $this->planet->getLevel($id)];
			}

			if ($this->user->getTechLevel($id) > 0) {
				$parse['slots']['attackers'][0][$id] = ['c' => $this->user->getTechLevel($id)];
			}
		}

		return $parse;
	}

	public function reportById(string $id)
	{
		$log = LogsSimulation::findOne($id);

		if (!$log) {
			throw new Exception('Лога не существует');
		}

		$result = $log->data;

		$report = new BattleReport($result[0], $result[1], $result[2], $result[3], $result[4], $result[5], $result[6]);
		$report = $report->report();

		return [
			'report' => $report,
			'uuid' => $log->id,
		];
	}

	public function report(Request $request)
	{
		$r = explode('|', $request->input('r', ''));

		if (empty($r[0]) || empty($r[10])) {
			throw new Exception('Нет данных для симуляции боя');
		}

		$sim = new Simulation();

		foreach ($r as $slot) {
			$items = [];

			foreach (explode(';', $slot) as $row) {
				$f = explode(',', $row);

				if (isset($f[1]) && $f[1] > 0) {
					$items[] = [
						'id' => (int) $f[0],
						'count' => (int) $f[1],
					];
				}
			}

			$sim->addSlot($items);
		}

		$result = $sim->getResult();

		$report = new BattleReport($result[0], $result[1], $result[2], $result[3], $result[4], $result[5], $result[6]);
		$report = $report->report();

		$log = LogsSimulation::create([
			'data' => $result,
		]);

		return [
			'report' => $report,
			'statistics' => $sim->getStatistics(),
			'uuid' => $log->id,
		];
	}
}
