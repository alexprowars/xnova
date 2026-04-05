<?php

namespace App\Services;

use App\Engine\Entity\Model\FleetEntityCollection;
use App\Facades\Vars;
use App\Format;
use App\Models\HallOfFame;
use App\Models\LogsBattle;
use App\Models\Planet;
use App\Models\Report;

class FleetService
{
	public static function convertFleetToDebris(FleetEntityCollection $fleets): array
	{
		$debris = ['metal' => 0, 'crystal' => 0];

		foreach ($fleets as $entity) {
			$res = Vars::getItemPrice($entity->id);

			if (!empty($res['metal']) && $res['metal'] > 0) {
				$debris['metal'] += floor($entity->count * $res['metal'] * config('game.fleetDebrisRate', 0));
			}

			if (!empty($res['crystal']) && $res['crystal'] > 0) {
				$debris['crystal'] += floor($entity->count * $res['crystal'] * config('game.fleetDebrisRate', 0));
			}
		}

		return $debris;
	}

	public static function getSteal(Planet $planet, int $capacity = 0)
	{
		$steal = ['metal' => 0, 'crystal' => 0, 'deuterium' => 0];

		if ($capacity > 0) {
			$metal 		= $planet->metal / 2;
			$crystal 	= $planet->crystal / 2;
			$deuterium 	= $planet->deuterium / 2;

			$steal['metal'] = (int) min($capacity / 3, $metal);
			$capacity -= $steal['metal'];

			$steal['crystal'] = (int) min($capacity / 2, $crystal);
			$capacity -= $steal['crystal'];

			$steal['deuterium'] = (int) min($capacity, $deuterium);
			$capacity -= $steal['deuterium'];

			if ($capacity > 0) {
				$oldStealMetal = $steal['metal'];

				$steal['metal'] += (int) min(($capacity / 2), ($metal - $steal['metal']));
				$capacity -= $steal['metal'] - $oldStealMetal;

				$steal['crystal'] += (int) min($capacity, ($crystal - $steal['crystal']));
			}
		}

		return array_map(
			fn(int $value) => (int) max($value, 0),
			$steal
		);
	}

	public static function checkHallBattle(Report $report): void
	{
		$lost = $report['data']['result']['lost']['att'] + $report['data']['result']['lost']['def'];

		if (config('game.hallPoints') !== null && $lost < config('game.hallPoints')) {
			return;
		}

		$type = 'single';

		$userList = [];

		foreach ($report['data']['attackers'] as $info) {
			if (!in_array($info['username'], $userList)) {
				$userList[] = $info['username'];
			}
		}

		if (count($userList) > 1) {
			$type = 'team';
		}

		$title_1 = implode(',', $userList);

		$userList = [];

		foreach ($report['data']['defenders'] as $info) {
			if (!in_array($info['username'], $userList)) {
				$userList[] = $info['username'];
			}
		}

		if (count($userList) > 1) {
			$type = 'team';
		}

		$title_2 = implode(',', $userList);

		$title = $title_1 . ' vs ' . $title_2 . ' (' . Format::number($lost) . ')';

		$battleLog = new LogsBattle();
		$battleLog->title = $title;
		$battleLog->data = $report->data;

		if ($battleLog->save()) {
			HallOfFame::create([
				'title' 	=> $title,
				'debris' 	=> floor($lost / 1000),
				'date' 		=> now(),
				'won' 		=> $report['data']['result']['won'],
				'type' 		=> $type,
				'report_id' => $battleLog->id,
			]);
		}
	}
}
