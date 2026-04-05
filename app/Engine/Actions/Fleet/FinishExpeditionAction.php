<?php

namespace App\Engine\Actions\Fleet;

use App\Engine\Battle\Battle;
use App\Engine\Entity\Model\FleetEntity;
use App\Engine\Entity\Model\FleetEntityCollection;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\MessageType;
use App\Engine\Fleet\FleetEngine;
use App\Engine\Fleet\Missions\Attack;
use App\Engine\Game;
use App\Facades\Vars;
use App\Format;
use App\Models\Fleet;
use App\Models\Report;
use App\Models\Statistic;
use App\Models\User;
use App\Notifications\MessageNotification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class FinishExpeditionAction
{
	public function __construct(protected Fleet $fleet)
	{
	}

	public function handle()
	{
		switch (random_int(1, 10)) {
			case 1:
				$this->eventFindGoods();
				break;
			case 2:
				$this->eventFindCredits();
				break;
			case 3:
				$this->eventFindShips();
				break;
			case 4:
				$this->eventAttack();
				break;
			case 5:
				$this->fleet->delete();

				$message = 'fleet_engine.sys_expe_lost_fleet_' . random_int(1, 4);
				$this->fleet->user->notify(new MessageNotification(null, MessageType::Expedition, 'fleet_engine.sys_expe_report', $message));

				break;

			case 6:
				$this->eventExtendTime();
				break;

			default:
				$this->return();

				$message = 'fleet_engine.sys_expe_nothing_' . random_int(1, 8);
				$this->fleet->user->notify(new MessageNotification(null, MessageType::Expedition, 'fleet_engine.sys_expe_report', $message));
		}
	}

	public function return(): void
	{
		(new FleetEngine($this->fleet))->return();
	}

	protected function determineEventSize(): int
	{
		$size = random_int(0, 99);

		if (10 < $size) {
			return 0; // 89%
		} elseif (0 < $size) {
			return 1; // 10%
		}

		return 2; // 1%
	}

	protected function getUpperLimit(): int
	{
		$statFactor = Statistic::query()->where('stat_type', 1)->max('total_points');

		if ($statFactor < 10000) {
			return 200;
		} elseif ($statFactor < 100000) {
			return 2400;
		} elseif ($statFactor < 1000000) {
			return 6000;
		} elseif ($statFactor < 5000000) {
			return 9000;
		}

		return 12000;
	}

	protected function getFleetPoints(): int
	{
		$expowert = [];

		foreach (Vars::getItemsByType(ItemType::FLEET) as $id) {
			$expowert[$id] = Vars::getItemTotalPrice($id) / 200;
		}

		$points = 0;

		foreach ($this->fleet->entities as $entity) {
			$points += $entity->count * $expowert[$entity->id];
		}

		return $points;
	}

	protected function eventFindGoods(): void
	{
		$witchFound = random_int(1, 3);

		switch ($this->determineEventSize()) {
			case 2:
				$factor = (random_int(100, 200) / $witchFound) * (1 + (Game::getSpeed('mine') - 1) / 10);
				$message = 'fleet_engine.sys_expe_found_ress_3_' . random_int(1, 2);
				break;
			case 1:
				$factor = (random_int(50, 100) / $witchFound) * (1 + (Game::getSpeed('mine') - 1) / 10);
				$message = 'fleet_engine.sys_expe_found_ress_2_' . random_int(1, 3);
				break;
			default:
				$factor = (random_int(10, 50) / $witchFound) *  (1 + (Game::getSpeed('mine') - 1) / 10);
				$message = 'fleet_engine.sys_expe_found_ress_1_' . random_int(1, 4);
		}

		$fleetPoints = $this->getFleetPoints();
		$upperLimit = $this->getUpperLimit();

		$fleetCapacity  = $this->fleet->entities->getCapacity();
		$fleetCapacity -= $this->fleet->resource_metal + $this->fleet->resource_crystal + $this->fleet->resource_deuterium;

		$size = min($factor * max(min($fleetPoints, $upperLimit), 200), $fleetCapacity);

		$update = [];

		switch ($witchFound) {
			case 1:
				$update['resource_metal'] = $size;
				break;
			case 2:
				$update['resource_crystal'] = $size;
				break;
			case 3:
				$update['resource_deuterium'] = $size;
				break;
		}

		Fleet::query()->whereKey($this->fleet)
			->incrementEach($update);

		$this->return();

		$this->fleet->user->notify(new MessageNotification(null, MessageType::Expedition, 'fleet_engine.sys_expe_report', $message));
	}

	protected function eventFindCredits(): void
	{
		$size = match ($this->determineEventSize()) {
			2 => random_int(5, 10),
			1 => random_int(2, 5),
			default => random_int(1, 2),
		};

		$this->fleet->user->increment('credits', $size);
		$this->return();

		$message = 'fleet_engine.sys_expe_found_dm_1_' . random_int(1, 5);

		$this->fleet->user->notify(new MessageNotification(null, MessageType::Expedition, 'fleet_engine.sys_expe_report', $message));
	}

	protected function eventFindShips(): void
	{
		$fleetPoints = $this->getFleetPoints();
		$upperLimit = $this->getUpperLimit();

		$eventType = $this->determineEventSize();

		$message = [
			'type' => 'ExpeditionFoundShipsMessage',
			'event_type' => $eventType,
		];

		switch ($this->determineEventSize()) {
			case 2:
				$size = random_int(102, 200);
				$message['event_subtype'] = random_int(1, 2);
				break;
			case 1:
				$size = random_int(52, 100);
				$message['event_subtype'] = random_int(1, 2);
				break;
			default:
				$size = random_int(10, 50);
				$message['event_subtype'] = random_int(1, 4);
		}

		$foundShips = max(round($size * min($fleetPoints, ($upperLimit / 2))), 10000);

		$newFleetArray = new FleetEntityCollection();

		$found = [];

		foreach (Vars::getItemsByType(ItemType::FLEET) as $id) {
			if (!$this->fleet->entities->getByEntityId($id) || $id == 208 || $id == 209 || $id == 214) {
				continue;
			}

			$maxFound = (int) floor($foundShips / Vars::getItemTotalPrice($id));

			if ($maxFound <= 0) {
				continue;
			}

			$count = random_int(0, $maxFound);

			if ($count <= 0) {
				continue;
			}

			$found[$id]	= $count;

			$foundShips -= $count * Vars::getItemTotalPrice($id);

			if ($foundShips <= 0) {
				break;
			}
		}

		foreach ($this->fleet->entities as $entity) {
			$newFleetArray->add(FleetEntity::create($entity->id, (int) ($entity->count + floor($found[$entity->id] ?? 0))));
		}

		$message['units'] = $found;

		$this->fleet->entities = $newFleetArray;
		$this->return();

		$this->fleet->user->notify(new MessageNotification(null, MessageType::Expedition, 'fleet_engine.sys_expe_report', $message));
	}

	protected function eventAttack(): void
	{
		$chance = random_int(1, 2);

		if ($chance == 1) {
			$points = [-3, -5, -8];
			$which = 1;
			$def = -3;
			$mame = __('fleet_engine.sys_expe_attackname_1');
			$add = 0;
			$rand = [5, 3, 2];
			$defenderFleetArray = [
				[204 => 5],
				[206 => 3],
				[207 => 2],
			];
		} else {
			$points = [-4, -6, -9];
			$which = 2;
			$def = 3;
			$mame = __('fleet_engine.sys_expe_attackname_2');
			$add = 0.1;
			$rand = [4, 3, 2];
			$defenderFleetArray = [
				[205 => 5],
				[207 => 5],
				[213 => 2],
			];
		}

		switch ($this->determineEventSize()) {
			case 2:
				$message = __('fleet_engine.sys_expe_attack_' . $which . '_3_' . $rand[2]);
				$maxAttackerPoints = 0.3 + $add + (random_int($points[2], abs($points[2])) * 0.01);
				break;
			case 1:
				$message = __('fleet_engine.sys_expe_attack_' . $which . '_2_' . $rand[1]);
				$maxAttackerPoints = 0.3 + $add + (random_int($points[1], abs($points[1])) * 0.01);
				break;
			default:
				$message = __('fleet_engine.sys_expe_attack_' . $which . '_1_' . $rand[0]);
				$maxAttackerPoints = 0.3 + $add + (random_int($points[0], abs($points[0])) * 0.01);
		}

		foreach ($this->fleet->entities as $entity) {
			$defenderFleetArray[$entity->id] = (int) round($entity->count * $maxAttackerPoints);
		}

		$battle = new Battle();
		$battle->addAttackerFleet($this->fleet);

		$alienUser = new User(['id' => 0, 'username' => $mame]);

		$defenderFleet = new Fleet();
		$defenderFleet->entities = FleetEntityCollection::createFromArray($defenderFleetArray);
		$defenderFleet->user()->associate($alienUser);
		$defenderFleet->end_galaxy = $this->fleet->end_galaxy;
		$defenderFleet->end_system = $this->fleet->end_system;
		$defenderFleet->end_planet = $this->fleet->end_planet;

		$battle->addDefenderFleet($defenderFleet);
		$battle->setRounds(6);

		Config::set('settings.repairDefenceFactor', 0);
		Config::set('settings.battleRounds', 6);

		$report = $battle->run();

		$result = $report->convertToArray();

		$attackUsers 	= $report->convertPlayerGroupToArray($report->getResultAttackersFleetOnRound('START'));
		$defenseUsers 	= $report->convertPlayerGroupToArray($report->getResultDefendersFleetOnRound('START'));

		$result['debris']['att'] = [0, 0];
		$result['debris']['def'] = [0, 0];

		$attackFleets = (new Attack($this->fleet))
			->getResultFleetArray($report->getPresentationAttackersFleetOnRound('START'), $report->getAfterBattleAttackers());

		foreach ($attackFleets as $fleetID => $attacker) {
			$fleetArray = FleetEntityCollection::createFromArray($attacker);

			$query = Fleet::query()
				->whereKey($fleetID);

			if ($fleetArray->isEmpty()) {
				$query->delete();
			} else {
				$query->update([
					'entities'		=> $fleetArray,
					'updated_at' 	=> DB::raw('end_date'),
					'mess'			=> 1,
					'won'			=> $result['won'],
				]);
			}
		}

		$raport = [
			'result' => $result,
			'attackers' => $attackUsers,
			'defenders' => $defenseUsers,
			'steal' => ['metal' => 0, 'crystal' => 0, 'deuterium' => 0],
			'moon_chance' => 0,
			'moon' => null,
			'repair' => [],
		];

		$report = Report::create([
			'users_id' => $report->getAttackersId(),
			'no_contact' => false,
			'data' => $raport,
		]);

		$colorAtt = $colorDef = '';

		switch ($result['won']) {
			case 2:
				$colorAtt = 'red';
				$colorDef = 'green';
				break;
			case 0:
				$colorAtt = 'orange';
				$colorDef = 'orange';
				break;
			case 1:
				$colorAtt = 'green';
				$colorDef = 'red';
				break;
		}

		$messageAtt = sprintf(
			'<a href="%s" target="_blank"><center><span style="color: %s">%s %s</span></a><br><br><span style="color: %s">%s: %s</span> <span style="color: %s">%s: %s</span><br>%s %s:<span style="color: #adaead">%s</span> %s:<span style="color: #ef51ef">%s</span> %s:<span style="color: #f77542">%s</span><br>%s %s:<span style="color: #adaead">%s</span> %s:<span style="color: #ef51ef">%s</span><br></center>',
			'/rw/' . $report->id . '?signature=' . Crypt::encrypt($report->id),
			$colorAtt,
			'Боевой доклад',
			__('main.sys_adress_planet', [
				'galaxy' => $this->fleet->end_galaxy,
				'system' => $this->fleet->end_system,
				'planet' => $this->fleet->end_planet,
			]),
			$colorAtt,
			__('fleet_engine.sys_perte_attaquant'),
			Format::number($result['lost']['att']),
			$colorDef,
			__('fleet_engine.sys_perte_defenseur'),
			Format::number($result['lost']['def']),
			__('fleet_engine.sys_gain'),
			__('main.metal'),
			0,
			__('main.crystal'),
			0,
			__('main.deuterium'),
			0,
			__('fleet_engine.sys_debris'),
			__('main.metal'),
			0,
			__('main.crystal'),
			0
		);

		$this->fleet->user->notify(new MessageNotification(null, MessageType::Battle, 'fleet_engine.sys_mess_tower', $messageAtt));
		$this->fleet->user->notify(new MessageNotification(null, MessageType::Expedition, 'fleet_engine.sys_expe_report', $message));
	}

	protected function eventExtendTime(): void
	{
		$MoreTime = random_int(0, 100);
		$Wrapper = [2, 2, 2, 2, 2, 2, 2, 3, 3, 5];

		if ($MoreTime < 75) {
			$this->fleet->end_date->addSeconds((($this->fleet->end_stay?->getTimestamp() ?? 0) - $this->fleet->start_date->getTimestamp()) * (array_rand($Wrapper) - 1));

			$message = 'fleet_engine.sys_expe_time_slow_' . random_int(1, 6);
		} else {
			$this->fleet->end_date->subSeconds(max(1, ((($this->fleet->end_stay?->getTimestamp() ?? 0) - $this->fleet->start_date->getTimestamp()) / 3 * array_rand($Wrapper))));

			$message = 'fleet_engine.sys_expe_time_fast_' . random_int(1, 3);
		}

		$this->fleet->user->notify(new MessageNotification(null, MessageType::Expedition, 'fleet_engine.sys_expe_report', $message));
		$this->return();
	}
}
