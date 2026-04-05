<?php

namespace App\Http\Controllers;

use App\Engine\Battle\BattleReport;
use App\Engine\Battle\Simulation;
use App\Engine\Enums\ItemType;
use App\Exceptions\Exception;
use App\Exceptions\PageException;
use App\Facades\Vars;
use App\Models\LogsSimulation;
use Illuminate\Http\Request;
use Throwable;

class SimController extends Controller
{
	public function index(Request $request)
	{
		if ($request->has('units')) {
			$units = explode(';', $request->input('units'));
		} else {
			$units = [];
		}

		$units = array_filter($units);

		$maxSlots = config('game.maxSlotsInSim', 5);

		$result = [
			'tech' => [109, 110, 111, 120, 121, 122],
			'slots' => [
				'max' => $maxSlots,
				'attackers' => [
					0 => [],
				],
				'defenders' => [
					0 => [],
				],
			]
		];

		foreach ($units as $row) {
			$element = explode(',', $row);

			if (!is_numeric($element[0])) {
				$element[0] = Vars::getIdByName($element[0]);
			}

			if (isset($element[1])) {
				$result['slots']['defenders'][0][$element[0]] = ['c' => $element[1]];
			}
		}

		$res = Vars::getItemsByType([ItemType::FLEET, ItemType::DEFENSE, ItemType::TECH]);

		foreach ($res as $id) {
			if ($this->planet->getLevel($id) > 0) {
				$result['slots']['attackers'][0][$id] = ['c' => $this->planet->getLevel($id)];
			}

			if ($this->user->getTechLevel($id) > 0) {
				$result['slots']['attackers'][0][$id] = ['c' => $this->user->getTechLevel($id)];
			}
		}

		return $result;
	}

	public function reportById(string $id)
	{
		$log = LogsSimulation::findOne($id);

		if (!$log) {
			throw new Exception('Лога не существует');
		}

		$result = $log->data;

		try {
			$report = BattleReport::fromArray($result)->report();
		} catch (Throwable) {
			throw new PageException('Ошибка обработки боевого отчета');
		}

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

		try {
			$report = BattleReport::fromArray($result)->report();
		} catch (Throwable) {
			throw new PageException('Ошибка обработки боевого отчета');
		}

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
