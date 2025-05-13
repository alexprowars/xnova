<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\CombatEngine\Core\Battle;
use App\Engine\CombatEngine\LangImplementation;
use App\Engine\CombatEngine\Models\Fleet;
use App\Engine\CombatEngine\Models\Player;
use App\Engine\CombatEngine\Models\PlayerGroup;
use App\Engine\CombatEngine\Utils\LangManager;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\MessageType;
use App\Engine\Vars;
use App\Format;
use App\Models;
use App\Models\Statistic;
use App\Notifications\MessageNotification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class Expedition extends BaseMission
{
	public function targetEvent()
	{
		$this->stayFleet();
	}

	public function endStayEvent()
	{
		$expowert = [];

		foreach (Vars::getItemsByType(ItemType::FLEET) as $id) {
			$expowert[$id] = Vars::getItemTotalPrice($id) / 200;
		}

		$fleetPoints = 0;

		$fleetCapacity = 0;
		$fleetCount = [];

		foreach ($this->fleet->getShips() as $type => $ship) {
			$fleetCount[$type] = $ship['count'];

			$fleetData = Vars::getUnitData($type);

			$fleetCapacity += $ship['count'] * $fleetData['capacity'];
			$fleetPoints += $ship['count'] * $expowert[$type];
		}

		$statFactor = Statistic::query()->where('stat_type', 1)->max('total_points');

		if ($statFactor < 10000) {
			$upperLimit = 200;
		} elseif ($statFactor < 100000) {
			$upperLimit = 2400;
		} elseif ($statFactor < 1000000) {
			$upperLimit = 6000;
		} elseif ($statFactor < 5000000) {
			$upperLimit = 9000;
		} else {
			$upperLimit = 12000;
		}

		$fleetCapacity -= $this->fleet->resource_metal + $this->fleet->resource_crystal + $this->fleet->resource_deuterium;

		switch (random_int(1, 10)) {
			case 1:
				$witchFound = random_int(1, 3);

				switch ($this->determineEventSize()) {
					case 2:
						$factor = (random_int(100, 200) / $witchFound) * (1 + (config('game.resource_multiplier') - 1) / 10);
						$message = __('fleet_engine.sys_expe_found_ress_3_' . random_int(1, 2));
						break;
					case 1:
						$factor = (random_int(50, 100) / $witchFound) * (1 + (config('game.resource_multiplier') - 1) / 10);
						$message = __('fleet_engine.sys_expe_found_ress_2_' . random_int(1, 3));
						break;
					default:
						$factor = (random_int(10, 50) / $witchFound) *  (1 + (config('game.resource_multiplier') - 1) / 10);
						$message = __('fleet_engine.sys_expe_found_ress_1_' . random_int(1, 4));
				}

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

				Models\Fleet::query()->whereKey($this->fleet)
					->incrementEach($update);

				$this->return();

				break;

			case 2:
				$size = match ($this->determineEventSize()) {
					2 => random_int(5, 10),
					1 => random_int(2, 5),
					default => random_int(1, 2),
				};

				$message = __('fleet_engine.sys_expe_found_dm_1_' . random_int(1, 5));

				$this->fleet->user->increment('credits', $size);
				$this->return();

				break;

			case 3:
				switch ($this->determineEventSize()) {
					case 2:
						$size = random_int(102, 200);
						$message = __('fleet_engine.sys_expe_found_ships_3_' . random_int(1, 2));
						break;
					case 1:
						$size = random_int(52, 100);
						$message = __('fleet_engine.sys_expe_found_ships_2_' . random_int(1, 2));
						break;
					default:
						$size = random_int(10, 50);
						$message = __('fleet_engine.sys_expe_found_ships_1_' . random_int(1, 4));
				}

				$foundShips = max(round($size * min($fleetPoints, ($upperLimit / 2))), 10000);

				$foundShipMess = '';
				$newFleetArray = [];

				$found = [];

				foreach (Vars::getItemsByType(ItemType::FLEET) as $id) {
					if (!isset($fleetCount[$id]) || $id == 208 || $id == 209 || $id == 214) {
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

					$foundShips	 	-= $count * Vars::getItemTotalPrice($id);
					$foundShipMess  .= '<br>' . __('main.tech.' . $id) . ': ' . Format::number($count);

					if ($foundShips <= 0) {
						break;
					}
				}

				foreach ($fleetCount as $id => $count) {
					$newFleetArray[] = [
						'id' => (int) $id,
						'count' => (int) ($count + floor($found[$id] ?? 0)),
					];
				}

				$message .= $foundShipMess;

				$this->fleet->fleet_array = $newFleetArray;
				$this->return();

				break;

			case 4:
				$chance = random_int(1, 2);

				if ($chance == 1) {
					$points = [-3, -5, -8];
					$which = 1;
					$def = -3;
					$mame = __('fleet_engine.sys_expe_attackname_1');
					$add = 0;
					$rand = [5, 3, 2];
					$defenderFleetArray = [
						['id' => 204, 'count' => 5],
						['id' => 206, 'count' => 3],
						['id' => 207, 'count' => 2]
					];
				} else {
					$points = [-4, -6, -9];
					$which = 2;
					$def = 3;
					$mame = __('fleet_engine.sys_expe_attackname_2');
					$add = 0.1;
					$rand = [4, 3, 2];
					$defenderFleetArray = [
						['id' => 205, 'count' => 5],
						['id' => 207, 'count' => 5],
						['id' => 213, 'count' => 2]
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

				foreach ($fleetCount as $id => $count) {
					$defenderFleetArray[] = [
						'id' => $id,
						'count' => round($count * $maxAttackerPoints),
					];
				}

				LangManager::getInstance()->setImplementation(new LangImplementation());

				$mission = new Attack(new Models\Fleet());

				$attackers = new PlayerGroup();
				$defenders = new PlayerGroup();

				$mission->getGroupFleet($this->fleet, $attackers);

				$fleetData = $defenderFleetArray;

				$mission->usersInfo[0] = [];
				$mission->usersInfo[0][0] = [
					'galaxy' => $this->fleet->end_galaxy,
					'system' => $this->fleet->end_system,
					'planet' => $this->fleet->end_planet
				];

				$res = [];

				foreach ($fleetData as $shipId => $shipArr) {
					if (Vars::getItemType($shipId) != ItemType::FLEET) {
						continue;
					}

					$res[$shipId] = $shipArr['count'];
				}

				$playerObj = new Player(0);
				$playerObj->setName($mame);
				$playerObj->setTech(0, 0, 0);

				foreach (Vars::getItemsByType(ItemType::TECH) as $techId) {
					if (isset($mission->usersTech[$this->fleet->user_id][Vars::getName($techId)]) && $mission->usersTech[$this->fleet->user_id][Vars::getName($techId)] > 0) {
						$res[$techId] = random_int(abs($mission->usersTech[$this->fleet->user_id][Vars::getName($techId)] + $def), 0);
					}
				}

				$mission->usersTech[0] = $res;

				$fleetObj = new Fleet(0);

				foreach ($fleetData as $shipId => $shipArr) {
					if (Vars::getItemType($shipId) != ItemType::FLEET || !$shipArr['count']) {
						continue;
					}

					$fleetObj->addShipType($mission->getShipType($shipId, $shipArr['count'], $res));
				}

				if (!$fleetObj->isEmpty()) {
					$playerObj->addFleet($fleetObj);
				}

				$defenders->addPlayer($playerObj);

				Config::set('settings.repairDefenceFactor', 0);
				Config::set('settings.battleRounds', 6);

				$engine = new Battle($attackers, $defenders);

				$report = $engine->getReport();

				$result = ['version' => 2, 'time' => time(), 'rw' => []];

				$attackUsers 	= $mission->convertPlayerGroupToArray($report->getResultAttackersFleetOnRound('START'));
				$defenseUsers 	= $mission->convertPlayerGroupToArray($report->getResultDefendersFleetOnRound('START'));

				for ($_i = 0; $_i <= $report->getLastRoundNumber(); $_i++) {
					$result['rw'][] = $mission->convertRoundToArray($report->getRound($_i));
				}

				if ($report->attackerHasWin()) {
					$result['won'] = 1;
				}
				if ($report->defenderHasWin()) {
					$result['won'] = 2;
				}
				if ($report->isAdraw()) {
					$result['won'] = 0;
				}

				$result['lost'] = ['att' => $report->getTotalAttackersLostUnits(), 'def' => $report->getTotalDefendersLostUnits()];

				$result['debree']['att'] = [0,0];
				$result['debree']['def'] = [0,0];

				$attackFleets = $mission->getResultFleetArray($report->getPresentationAttackersFleetOnRound('START'), $report->getAfterBattleAttackers());

				foreach ($attackFleets as $fleetID => $attacker) {
					$fleetArray = [];

					foreach ($attacker as $element => $amount) {
						if (!is_numeric($element) || !$amount) {
							continue;
						}

						$fleetArray[] = [
							'id' => (int) $element,
							'count' => (int) $amount
						];
					}

					if (count($fleetArray)) {
						$this->killFleet($fleetID);
					} else {
						Models\Fleet::query()->whereKey($fleetID)
							->update([
								'fleet_array' 	=> $fleetArray,
								'updated_at' 	=> DB::raw('end_time'),
								'mess'			=> 1,
								'won'			=> $result['won']
							]);
					}
				}

				$fleetsUsers = [];

				foreach ($attackUsers as $info) {
					$fleetsUsers[] = $info['tech']['id'];
				}

				$raport = [
					$result,
					$attackUsers,
					$defenseUsers,
					['metal' => 0, 'crystal' => 0, 'deuterium' => 0],
					0,
					'',
					[]
				];

				$report = Models\Report::create([
					'users_id' => $fleetsUsers,
					'no_contact' => 0,
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
					'<a href="/rw/%s" target="_blank"><center><font color="%s">%s %s</font></a><br><br><font color="%s">%s: %s</font> <font color="%s">%s: %s</font><br>%s %s:<font color="#adaead">%s</font> %s:<font color="#ef51ef">%s</font> %s:<font color="#f77542">%s</font><br>%s %s:<font color="#adaead">%s</font> %s:<font color="#ef51ef">%s</font><br></center>',
					str_replace('/api', '', url()->signedRoute('log.view', ['id' => $report->id], absolute: false)),
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

				$this->fleet->user->notify(new MessageNotification(null, MessageType::Battle, __('fleet_engine.sys_mess_tower'), $messageAtt));

				break;

			case 5:
				$this->killFleet();

				$message = __('fleet_engine.sys_expe_lost_fleet_' . random_int(1, 4));

				break;

			case 6:
				$MoreTime = random_int(0, 100);
				$Wrapper = [];
				$Wrapper[] = 2;
				$Wrapper[] = 2;
				$Wrapper[] = 2;
				$Wrapper[] = 2;
				$Wrapper[] = 2;
				$Wrapper[] = 2;
				$Wrapper[] = 2;
				$Wrapper[] = 3;
				$Wrapper[] = 3;
				$Wrapper[] = 5;

				if ($MoreTime < 75) {
					$this->fleet->end_time->addSeconds((($this->fleet->end_stay?->getTimestamp() ?? 0) - $this->fleet->start_time->getTimestamp()) * (array_rand($Wrapper) - 1));

					$message = __('fleet_engine.sys_expe_time_slow_' . random_int(1, 6));
				} else {
					$this->fleet->end_time->subSeconds(max(1, ((($this->fleet->end_stay?->getTimestamp() ?? 0) - $this->fleet->start_time->getTimestamp()) / 3 * array_rand($Wrapper))));

					$message = __('fleet_engine.sys_expe_time_fast_' . random_int(1, 3));
				}

				$this->return();

				break;

			default:
				$this->return();

				$message = __('fleet_engine.sys_expe_nothing_' . random_int(1, 8));
		}

		$this->fleet->user->notify(new MessageNotification(null, MessageType::Expedition, __('fleet_engine.sys_expe_report'), $message));
	}

	public function returnEvent()
	{
		$message = __('fleet_engine.sys_expe_back_home', [
			'mt' => __('main.metal'),
			'm' => Format::number($this->fleet->resource_metal),
			'ct' => __('main.crystal'),
			'c' => Format::number($this->fleet->resource_crystal),
			'dt' => __('main.deuterium'),
			'd' => Format::number($this->fleet->resource_deuterium),
		]);

		$this->fleet->user->notify(new MessageNotification(null, MessageType::Expedition, __('fleet_engine.sys_expe_report'), $message));

		parent::returnEvent();
	}

	protected function determineEventSize(): int
	{
		$size = random_int(0, 99);

		if (10 < $size) {
			return 0; // 89%
		} elseif (0 < $size) {
			return 1; // 10%
		} else {
			return 2; // 1%
		}
	}
}
