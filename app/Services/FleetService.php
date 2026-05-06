<?php

namespace App\Services;

use App\Engine\Entity\Model\FleetEntityCollection;
use App\Engine\Fleet\MissionType;
use App\Format;
use App\Http\Resources\FleetRow;
use App\Models\Fleet;
use App\Models\HallOfFame;
use App\Models\LogsBattle;
use App\Models\Planet;
use App\Models\Report;
use App\Models\User;

class FleetService
{
	/**
	 * @param FleetEntityCollection $fleets
	 * @return array<'metal'|'crystal', int>
	 */
	public static function convertFleetToDebris(FleetEntityCollection $fleets): array
	{
		$debris = ['metal' => 0, 'crystal' => 0];

		foreach ($fleets as $entity) {
			$res = $entity->getObjectData()->getPrice();

			if (!empty($res['metal']) && $res['metal'] > 0) {
				$debris['metal'] += (int) floor($entity->count * $res['metal'] * config('game.combat.debrisFactor_FLEET', 0));
			}

			if (!empty($res['crystal']) && $res['crystal'] > 0) {
				$debris['crystal'] += (int) floor($entity->count * $res['crystal'] * config('game.combat.debrisFactor_DEFENSE', 0));
			}
		}

		return $debris;
	}

	/**
	 * @return array<string, int>
	 */
	public static function getSteal(Planet $planet, int $capacity = 0): array
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
			static fn(int $value) => (int) max($value, 0),
			$steal
		);
	}

	public static function checkHallBattle(Report $report): void
	{
		if (!config('game.combat.hallPoints')) {
			return;
		}

		$lost = array_sum($report->data['lost']);

		if ($lost < config('game.combat.hallPoints')) {
			return;
		}

		$type = 'single';

		$userList = [];

		foreach ($report['data']['attackers'] as $info) {
			if (!in_array($info['name'], $userList)) {
				$userList[] = $info['name'];
			}
		}

		if (count($userList) > 1) {
			$type = 'team';
		}

		$title_1 = implode(',', $userList);

		$userList = [];

		foreach ($report['data']['defenders'] as $info) {
			if (!in_array($info['name'], $userList)) {
				$userList[] = $info['name'];
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
				'won' 		=> $report['data']['won'],
				'type' 		=> $type,
				'report_id' => $battleLog->id,
			]);
		}
	}

	public static function list(User $user): array
	{
		$fleets = Fleet::query()
			->with(['user'])
			->whereBelongsTo($user)
			->orWhereBelongsTo($user, 'target')
			->get();

		$result = [];
		$aks = [];

		foreach ($fleets as $fleet) {
			if ($fleet->user_id == $user->id) {
				if ($fleet->start_date->isFuture()) {
					$result[] = FleetRow::make($fleet, 0, true);
				}

				if ($fleet->end_stay?->isFuture()) {
					$result[] = FleetRow::make($fleet, 1, true);
				}

				if (!($fleet->mission == MissionType::Colonization && $fleet->mess == 0)) {
					if (($fleet->end_date->isFuture() && $fleet->mission != MissionType::Stay) or ($fleet->mess == 1 && $fleet->mission == MissionType::Stay)) {
						$result[] = FleetRow::make($fleet, 2, true);
					}
				}

				if ($fleet->assault_id && !in_array($fleet->assault_id, $aks)) {
					$assaultFleets = Fleet::query()
						->where('assault_id', $fleet->assault_id)
						->whereNot('user_id', $user->id)
						->where('mess', 0)
						->get();

					foreach ($assaultFleets as $AKFleet) {
						$result[] = FleetRow::make($AKFleet, 0, false);
					}

					$aks[] = $fleet->assault_id;
				}
			} elseif ($fleet->mission != MissionType::Recycling) {
				if ($fleet->start_date->isFuture()) {
					$result[] = FleetRow::make($fleet, 0, false);
				}

				if ($fleet->mission == MissionType::StayAlly && $fleet->end_stay?->isFuture()) {
					$result[] = FleetRow::make($fleet, 1, false);
				}
			}
		}

		usort($result, fn ($a, $b) => $a['time'] <=> $b['time']);

		return $result;
	}
}
