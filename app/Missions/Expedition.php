<?php

/**
 * @author AlexPro
 * @copyright 2008 - 2019 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

namespace Xnova\Missions;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Xnova\Battle\Core\Battle;
use Xnova\Battle\LangImplementation;
use Xnova\Battle\Models\Player;
use Xnova\Battle\Models\PlayerGroup;
use Xnova\Battle\Models\Fleet;
use Xnova\Battle\Utils\LangManager;
use Xnova\FleetEngine;
use Xnova\Format;
use Xnova\Models\Statistic;
use Xnova\Models;
use Xnova\User;
use Xnova\Vars;

class Expedition extends FleetEngine implements Mission
{
	public function targetEvent()
	{
		$this->stayFleet();
	}

	public function endStayEvent()
	{
		$Expowert = [];

		foreach (Vars::getItemsByType(Vars::ITEM_TYPE_FLEET) as $ID) {
			$Expowert[$ID] = Vars::getItemTotalPrice($ID) / 200;
		}

		$FleetPoints = 0;

		$FleetCapacity = 0;
		$FleetCount = [];

		foreach ($this->fleet->getShips() as $type => $ship) {
			$FleetCount[$type] = $ship['count'];

			$fleetData = Vars::getUnitData($type);

			$FleetCapacity += $ship['count'] * $fleetData['capacity'];
			$FleetPoints += $ship['count'] * $Expowert[$type];
		}

		$StatFactor = Statistic::query()->where('stat_type', 1)->max('total_points');

		if ($StatFactor < 10000) {
			$upperLimit = 200;
		} elseif ($StatFactor < 100000) {
			$upperLimit = 2400;
		} elseif ($StatFactor < 1000000) {
			$upperLimit = 6000;
		} elseif ($StatFactor < 5000000) {
			$upperLimit = 9000;
		} else {
			$upperLimit = 12000;
		}

		$FleetCapacity -= $this->fleet->resource_metal + $this->fleet->resource_crystal + $this->fleet->resource_deuterium;
		$GetEvent = mt_rand(1, 10);

		switch ($GetEvent) {
			case 1:
				$WitchFound = mt_rand(1, 3);
				$FindSize = mt_rand(0, 100);

				if (10 < $FindSize) {
					$Factor = (mt_rand(10, 50) / $WitchFound) *  (1 + (config('settings.resource_multiplier') - 1) / 10);
					$Message = __('fleet_engine.sys_expe_found_ress_1_' . mt_rand(1, 4));
				} elseif (0 < $FindSize && 10 >= $FindSize) {
					$Factor = (mt_rand(50, 100) / $WitchFound) * (1 + (config('settings.resource_multiplier') - 1) / 10);
					$Message = __('fleet_engine.sys_expe_found_ress_2_' . mt_rand(1, 3));
				} else {
					$Factor = (mt_rand(100, 200) / $WitchFound) * (1 + (config('settings.resource_multiplier') - 1) / 10);
					$Message = __('fleet_engine.sys_expe_found_ress_3_' . mt_rand(1, 2));
				}

				$Size = min($Factor * max(min($FleetPoints, $upperLimit), 200), $FleetCapacity);

				$update = [];

				switch ($WitchFound) {
					case 1:
						$update['+resource_metal'] = $Size;
						break;
					case 2:
						$update['+resource_crystal'] = $Size;
						break;
					case 3:
						$update['+resource_deuterium'] = $Size;
						break;
				}

				$this->returnFleet($update);

				break;

			case 2:
				$FindSize = mt_rand(0, 100);
				if (10 < $FindSize) {
					$Size		= mt_rand(1, 2);
				} elseif (0 < $FindSize && 10 >= $FindSize) {
					$Size		= mt_rand(2, 5);
				} else {
					$Size	 	= mt_rand(5, 10);
				}

				$Message = __('fleet_engine.sys_expe_found_dm_1_' . mt_rand(1, 5));

				Models\User::query()->where('id', $this->fleet->owner)
					->update(['credits' => DB::raw('credits + ' . $Size)]);

				$this->returnFleet();

				break;

			case 3:
				$FindSize = mt_rand(0, 100);
				if (10 < $FindSize) {
					$Size = mt_rand(10, 50);
					$Message = __('fleet_engine.sys_expe_found_ships_1_' . mt_rand(1, 4));
				} elseif (0 < $FindSize && 10 >= $FindSize) {
					$Size = mt_rand(52, 100);
					$Message = __('fleet_engine.sys_expe_found_ships_2_' . mt_rand(1, 2));
				} else {
					$Size = mt_rand(102, 200);
					$Message = __('fleet_engine.sys_expe_found_ships_3_' . mt_rand(1, 2));
				}

				$FoundShips = max(round($Size * min($FleetPoints, ($upperLimit / 2))), 10000);

				$FoundShipMess = "";
				$NewFleetArray = [];

				$Found = [];

				foreach (Vars::getItemsByType(Vars::ITEM_TYPE_FLEET) as $ID) {
					if (!isset($FleetCount[$ID]) || $ID == 208 || $ID == 209 || $ID == 214) {
						continue;
					}

					$MaxFound = floor($FoundShips / Vars::getItemTotalPrice($ID));

					if ($MaxFound <= 0) {
						continue;
					}

					$Count = mt_rand(0, $MaxFound);

					if ($Count <= 0) {
						continue;
					}

					$Found[$ID]	= $Count;

					$FoundShips	 		-= $Count * Vars::getItemTotalPrice($ID);
					$FoundShipMess   	.= '<br>' . __('main.tech.' . $ID) . ': ' . Format::number($Count);

					if ($FoundShips <= 0) {
						break;
					}
				}

				foreach ($FleetCount as $ID => $Count) {
					$NewFleetArray[] = [
						'id' => (int) $ID,
						'count' => (int) ($Count + (isset($Found[$ID]) ? floor($Found[$ID]) : 0))
					];
				}

				$Message .= $FoundShipMess;

				$this->returnFleet(['fleet_array' => json_encode($NewFleetArray)]);

				break;

			case 4:
				$Chance = mt_rand(1, 2);

				if ($Chance == 1) {
					$Points = [-3, -5, -8];
					$Which = 1;
					$Def = -3;
					$Name = __('fleet_engine.sys_expe_attackname_1');
					$Add = 0;
					$Rand = [5, 3, 2];
					$defenderFleetArray = [
						['id' => 204, 'count' => 5],
						['id' => 206, 'count' => 3],
						['id' => 207, 'count' => 2]
					];
				} else {
					$Points = [-4, -6, -9];
					$Which = 2;
					$Def = 3;
					$Name = __('fleet_engine.sys_expe_attackname_2');
					$Add = 0.1;
					$Rand = [4, 3, 2];
					$defenderFleetArray = [
						['id' => 205, 'count' => 5],
						['id' => 207, 'count' => 5],
						['id' => 213, 'count' => 2]
					];
				}

				$FindSize = mt_rand(0, 100);

				if (10 < $FindSize) {
					$Message = __('fleet_engine.sys_expe_attack_' . $Which . '_1_' . $Rand[0]);
					$MaxAttackerPoints = 0.3 + $Add + (mt_rand($Points[0], abs($Points[0])) * 0.01);
				} elseif (0 < $FindSize && 10 >= $FindSize) {
					$Message = __('fleet_engine.sys_expe_attack_' . $Which . '_2_' . $Rand[1]);
					$MaxAttackerPoints = 0.3 + $Add + (mt_rand($Points[1], abs($Points[1])) * 0.01);
				} else {
					$Message = __('fleet_engine.sys_expe_attack_' . $Which . '_3_' . $Rand[2]);
					$MaxAttackerPoints = 0.3 + $Add + (mt_rand($Points[2], abs($Points[2])) * 0.01);
				}

				foreach ($FleetCount as $ID => $count) {
					$defenderFleetArray[] = [
						'id' => $ID,
						'count' => round($count * $MaxAttackerPoints),
					];
				}

				LangManager::getInstance()->setImplementation(new LangImplementation());

				$mission = new Attack(new \Xnova\Models\Fleet());

				$attackers = new PlayerGroup();
				$defenders = new PlayerGroup();

				$mission->getGroupFleet($this->fleet, $attackers);

				$fleetData = $this->fleet->getShips($defenderFleetArray);

				$mission->usersInfo[0] = [];
				$mission->usersInfo[0][0] = [
					'galaxy' => $this->fleet->end_galaxy,
					'system' => $this->fleet->end_system,
					'planet' => $this->fleet->end_planet
				];

				$res = [];

				foreach ($fleetData as $shipId => $shipArr) {
					if (Vars::getItemType($shipId) != Vars::ITEM_TYPE_FLEET) {
						continue;
					}

					$res[$shipId] = $shipArr['count'];
				}

				$playerObj = new Player(0);
				$playerObj->setName($Name);
				$playerObj->setTech(0, 0, 0);

				foreach (Vars::getItemsByType(Vars::ITEM_TYPE_TECH) as $techId) {
					if (isset($mission->usersTech[$this->fleet->owner][Vars::getName($techId)]) && $mission->usersTech[$this->fleet->owner][Vars::getName($techId)] > 0) {
						$res[$techId] = mt_rand(abs($mission->usersTech[$this->fleet->owner][Vars::getName($techId)] + $Def), 0);
					}
				}

				$mission->usersTech[0] = $res;

				$fleetObj = new Fleet(0);

				foreach ($fleetData as $shipId => $shipArr) {
					if (Vars::getItemType($shipId) != Vars::ITEM_TYPE_FLEET || !$shipArr['count']) {
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
						Models\Fleet::query()->where('id', $fleetID)
							->update([
								'fleet_array' 	=> json_encode($fleetArray),
								'update_time' 	=> DB::raw('end_time'),
								'mess'			=> 1,
								'won'			=> $result['won']
							]);
					}
				}

				$FleetsUsers = [];

				foreach ($attackUsers as $info) {
					$FleetsUsers[] = $info['tech']['id'];
				}

				$raport = json_encode([
					$result,
					$attackUsers,
					$defenseUsers,
					['metal' => 0, 'crystal' => 0, 'deuterium' => 0],
					0,
					'',
					[]
				]);

				$ids = Models\Report::query()->insertGetId([
					'time' => time(),
					'id_users' => json_encode($FleetsUsers),
					'no_contact' => 0,
					'raport' => addslashes($raport),
				]);

				$ColorAtt = $ColorDef = '';

				switch ($result['won']) {
					case 2:
						$ColorAtt = "red";
						$ColorDef = "green";
						break;
					case 0:
						$ColorAtt = "orange";
						$ColorDef = "orange";
						break;
					case 1:
						$ColorAtt = "green";
						$ColorDef = "red";
						break;
				}
				$MessageAtt = sprintf('<a href="/rw/%s/%s/" target="_blank"><center><font color="%s">%s %s</font></a><br><br><font color="%s">%s: %s</font> <font color="%s">%s: %s</font><br>%s %s:<font color="#adaead">%s</font> %s:<font color="#ef51ef">%s</font> %s:<font color="#f77542">%s</font><br>%s %s:<font color="#adaead">%s</font> %s:<font color="#ef51ef">%s</font><br></center>', $ids, md5(config('app.key') . $ids), $ColorAtt, 'Боевой доклад', sprintf(__('fleet_engine.sys_adress_planet'), $this->fleet->end_galaxy, $this->fleet->end_system, $this->fleet->end_planet), $ColorAtt, __('fleet_engine.sys_perte_attaquant'), Format::number($result['lost']['att']), $ColorDef, __('fleet_engine.sys_perte_defenseur'), Format::number($result['lost']['def']), __('fleet_engine.sys_gain'), __('main.Metal'), 0, __('main.Crystal'), 0, __('main.Deuterium'), 0, __('fleet_engine.sys_debris'), __('main.Metal'), 0, __('main.Crystal'), 0);

				User::sendMessage($this->fleet->owner, 0, $this->fleet->start_time, 3, __('fleet_engine.sys_mess_tower'), $MessageAtt);

				break;

			case 5:
				$this->killFleet();

				$Message = __('fleet_engine.sys_expe_lost_fleet_' . mt_rand(1, 4));

				break;

			case 6:
				$MoreTime       = mt_rand(0, 100);
				$Wrapper        = [];
				$Wrapper[]      = 2;
				$Wrapper[]      = 2;
				$Wrapper[]      = 2;
				$Wrapper[]      = 2;
				$Wrapper[]      = 2;
				$Wrapper[]      = 2;
				$Wrapper[]      = 2;
				$Wrapper[]      = 3;
				$Wrapper[]      = 3;
				$Wrapper[]      = 5;

				if ($MoreTime < 75) {
					$this->fleet->end_time += ($this->fleet->end_stay - $this->fleet->start_time) * (array_rand($Wrapper) - 1);

					$Message = __('fleet_engine.sys_expe_time_slow_' . mt_rand(1, 6));
				} else {
					$this->fleet->end_time -= max(1, (($this->fleet->end_stay - $this->fleet->start_time) / 3 * array_rand($Wrapper)));

					$Message = __('fleet_engine.sys_expe_time_fast_' . mt_rand(1, 3));
				}

				$this->returnFleet();

				break;

			default:
				$this->returnFleet();

				$Message = __('fleet_engine.sys_expe_nothing_' . mt_rand(1, 8));
		}

		User::sendMessage($this->fleet->owner, 0, $this->fleet->end_stay, 15, __('fleet_engine.sys_expe_report'), $Message);
	}

	public function returnEvent()
	{
		$Message = sprintf(__('fleet_engine.sys_expe_back_home'), __('main.Metal'), Format::number($this->fleet->resource_metal), __('main.Crystal'), Format::number($this->fleet->resource_crystal), __('main.Deuterium'), Format::number($this->fleet->resource_deuterium));

		User::sendMessage($this->fleet->owner, 0, $this->fleet->end_time, 15, __('fleet_engine.sys_expe_report'), $Message);

		$this->restoreFleetToPlanet();
		$this->killFleet();
	}
}
