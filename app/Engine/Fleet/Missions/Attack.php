<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\CombatEngine\Core\Battle;
use App\Engine\CombatEngine\Core\Round;
use App\Engine\CombatEngine\LangImplementation;
use App\Engine\CombatEngine\Models\Defense;
use App\Engine\CombatEngine\Models\Fleet;
use App\Engine\CombatEngine\Models\HomeFleet;
use App\Engine\CombatEngine\Models\Player;
use App\Engine\CombatEngine\Models\PlayerGroup;
use App\Engine\CombatEngine\Models\Ship;
use App\Engine\CombatEngine\Models\ShipType;
use App\Engine\CombatEngine\Utils\LangManager;
use App\Engine\Coordinates;
use App\Engine\Enums\FleetDirection;
use App\Engine\Enums\ItemType;
use App\Engine\Enums\MessageType;
use App\Engine\Enums\PlanetType;
use App\Engine\Fleet\FleetEngine;
use App\Engine\Fleet\Mission as MissionEnum;
use App\Engine\Galaxy;
use App\Engine\QueueManager;
use App\Engine\Vars;
use App\Format;
use App\Models;
use App\Models\Fleet as FleetModel;
use App\Models\LogAttack;
use App\Models\Planet;
use App\Models\User;
use App\Notifications\MessageNotification;
use Illuminate\Support\Facades\DB;

class Attack extends FleetEngine implements Mission
{
	public $usersTech = [];
	public $usersInfo = [];

	public function targetEvent()
	{
		$target = Planet::findByCoordinates($this->fleet->getDestinationCoordinates());

		if (empty($target->id) || !$target->user_id || $target->destruyed) {
			$this->fleet->return();

			return false;
		}

		$owner = User::find($this->fleet->user_id);

		if (!$owner) {
			$this->fleet->return();

			return false;
		}

		$targetUser = User::find($target->user_id);

		if (!$targetUser) {
			$this->fleet->return();

			return false;
		}

		$target->setRelation('user', $targetUser);
		$target->getProduction($this->fleet->start_time)->update();

		$queueManager = new QueueManager($targetUser, $target);
		$queueManager->checkUnitQueue();

		LangManager::getInstance()->setImplementation(new LangImplementation());

		$attackers = new PlayerGroup();
		$defenders = new PlayerGroup();

		$this->getGroupFleet($this->fleet, $attackers);

		if ($this->fleet->assault_id) {
			$fleets = Models\Fleet::where('id', $this->fleet->id)
				->where('assault_id', $this->fleet->assault_id)
				->get();

			foreach ($fleets as $fleet) {
				$this->getGroupFleet($fleet, $attackers);
			}
		}

		$fleets = Models\Fleet::query()
			->coordinates(FleetDirection::END, $this->fleet->getDestinationCoordinates())
			->where('mess', 3)
			->get();

		foreach ($fleets as $fleet) {
			$this->getGroupFleet($fleet, $defenders);
		}

		$res = [];

		$units = Vars::getItemsByType([ItemType::FLEET, ItemType::DEFENSE]);

		foreach ($units as $i) {
			if ($target->getLevel($i) > 0) {
				$res[$i] = $target->getLevel($i);

				$l = $i > 400 ? ($i - 50) : ($i + 100);

				if ($targetUser->getTechLevel($l) > 0) {
					$res[$l] = $targetUser->getTechLevel($l);
				}
			}
		}

		foreach (Vars::getItemsByType(ItemType::TECH) as $techId) {
			$level = $targetUser->getTechLevel($techId);

			if ($targetUser->rpg_komandir?->isFuture() && in_array(Vars::getName($techId), ['military_tech', 'defence_tech', 'shield_tech'])) {
				$level += 2;
			}

			if ($level > 0) {
				$res[$techId] = $level;
			}
		}

		$this->usersTech[$targetUser->id] = $res;

		$homeFleet = new HomeFleet(0);

		foreach ($units as $i) {
			if ($target->getLevel($i) > 0) {
				$shipType = $this->getShipType($i, $target->getLevel($i), $res);

				if ($targetUser->rpg_ingenieur && $shipType->getType() == 'Ship') {
					$shipType->setRepairProb(0.8);
				}

				$homeFleet->addShipType($shipType);
			}
		}

		if (!$defenders->existPlayer($targetUser->id)) {
			$player = new Player($targetUser->id, [$homeFleet]);
			$player->setTech(0, 0, 0);
			$player->setName($targetUser->username);
			$defenders->addPlayer($player);

			if (!isset($this->usersInfo[$targetUser->id])) {
				$this->usersInfo[$targetUser->id] = [];
			}

			$this->usersInfo[$targetUser->id][0] = [
				'galaxy' => $target->galaxy,
				'system' => $target->system,
				'planet' => $target->planet
			];
		} else {
			$defenders->getPlayer($targetUser->id)->addDefense($homeFleet);
		}

		if (!$this->fleet->raunds) {
			$this->fleet->raunds = 6;
		}

		$engine = new Battle($attackers, $defenders, $this->fleet->raunds);
		$report = $engine->getReport();
		$result = ['version' => 2, 'time' => time(), 'rw' => []];

		$attackUsers 	= $this->convertPlayerGroupToArray($report->getResultAttackersFleetOnRound('START'));
		$defenseUsers 	= $this->convertPlayerGroupToArray($report->getResultDefendersFleetOnRound('START'));

		for ($_i = 0; $_i <= $report->getLastRoundNumber(); $_i++) {
			$result['rw'][] = $this->convertRoundToArray($report->getRound($_i));
		}

		$result['won'] = 0;

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

		$debris = $report->getDebris();

		$result['debree']['att'] = $debris;
		$result['debree']['def'] = [0, 0];

		$attackFleets 	= $this->getResultFleetArray($report->getPresentationAttackersFleetOnRound('START'), $report->getAfterBattleAttackers());
		$defenseFleets 	= $this->getResultFleetArray($report->getPresentationDefendersFleetOnRound('START'), $report->getAfterBattleDefenders());

		$repairFleets = [];

		foreach ($report->getDefendersRepaired() as $_player) {
			foreach ($_player as $_idFleet => $_fleet) {
				/**
				 * @var ShipType $_ship
				 */
				foreach ($_fleet as $_shipID => $_ship) {
					$repairFleets[$_idFleet][$_shipID] = $_ship->getCount();
				}
			}
		}

		$fleetToUser = [];

		foreach ($report->getPresentationAttackersFleetOnRound('START') as $idPlayer => $player) {
			/**
			 * @var $player Player
			 */
			foreach ($player->getIterator() as $idFleet => $fleet) {
				$fleetToUser[$idFleet] = $idPlayer;
			}
		}

		$steal = ['metal' => 0, 'crystal' => 0, 'deuterium' => 0];

		if ($result['won'] == 1) {
			$max_resources = 0;
			$max_fleet_res = [];

			foreach ($attackFleets as $fleet => $arr) {
				$max_fleet_res[$fleet] = 0;

				foreach ($arr as $Element => $amount) {
					if ($Element == 210) {
						continue;
					}

					$fleetData = Vars::getUnitData($Element);

					$capacity = $fleetData['capacity'] * $amount;

					$max_resources += $capacity;
					$max_fleet_res[$fleet] += $capacity;
				}
			}

			$res_correction = $max_resources;
			$res_procent = [];

			if ($max_resources > 0) {
				foreach ($max_fleet_res as $id => $res) {
					$res_procent[$id] = $res / $res_correction;
				}
			}

			$steal = $this->getSteal($target, $max_resources);
		}

		$totalDebree = $result['debree']['def'][0] + $result['debree']['def'][1] + $result['debree']['att'][0] + $result['debree']['att'][1];

		if ($totalDebree > 0) {
			Planet::coordinates(new Coordinates($target->galaxy, $target->system, $target->planet))
				->where('planet_type', '!=', PlanetType::MOON)
				->update([
					'debris_metal' => DB::raw('debris_metal + ' . ($result['debree']['att'][0] + $result['debree']['def'][0])),
					'debris_crystal' => DB::raw('debris_metal + ' . ($result['debree']['att'][1] + $result['debree']['def'][1])),
				]);
		}

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

			if (!count($fleetArray)) {
				$this->killFleet($fleetID);
			} else {
				$update = [
					'fleet_array' 	=> $fleetArray,
					'updated_at' 	=> DB::raw('end_time'),
					'mess'			=> 1,
					'assault_id'	=> null,
					'won'			=> $result['won']
				];

				if ($result['won'] == 1 && isset($res_procent[$fleetID]) && ($steal['metal'] > 0 || $steal['crystal'] > 0 || $steal['deuterium'] > 0)) {
					$update['resource_metal'] 		= DB::raw('resource_metal + ' . (int) round($res_procent[$fleetID] * $steal['metal']));
					$update['resource_crystal'] 	= DB::raw('resource_crystal + ' . (int) round($res_procent[$fleetID] * $steal['crystal']));
					$update['resource_deuterium'] 	= DB::raw('resource_deuterium + ' . (int) round($res_procent[$fleetID] * $steal['deuterium']));
				}

				Models\Fleet::query()->where('id', $fleetID)->update($update);
			}
		}

		foreach ($defenseFleets as $fleetID => $defender) {
			if ($fleetID != 0) {
				$fleetArray = [];

				foreach ($defender as $element => $amount) {
					if (!is_numeric($element) || !$amount) {
						continue;
					}

					$fleetArray[] = [
						'id' => (int) $element,
						'count' => (int) $amount
					];
				}

				if (!count($fleetArray)) {
					$this->killFleet($fleetID);
				} else {
					Planet::find($fleetID)
						->update([
							'fleet_array' => $fleetArray,
							'updated_at' => DB::raw('end_time'),
						]);
				}
			} else {
				if ($steal['metal'] > 0 || $steal['crystal'] > 0 || $steal['deuterium'] > 0) {
					$target->metal -= $steal['metal'];
					$target->crystal -= $steal['crystal'];
					$target->deuterium -= $steal['deuterium'];
				}

				foreach ($units as $i) {
					if (isset($defender[$i]) && $target->getLevel($i) > 0) {
						$target->updateAmount($i, $defender[$i]);
					}
				}

				$target->update();
			}
		}

		$moonChance = $report->getMoonProb(($targetUser->rpg_admiral?->isFuture() ? 10 : 0));

		if ($target->planet_type != PlanetType::PLANET) {
			$moonChance = 0;
		}

		$userChance = random_int(1, 100);

		if ($this->fleet->end_type == PlanetType::MILITARY_BASE) {
			$userChance = 0;
		}

		if ($target->parent_planet == 0 && $userChance && $userChance <= $moonChance) {
			$galaxy = new Galaxy();

			$planetId = $galaxy->createMoon(
				$this->fleet->getDestinationCoordinates(),
				$target->user_id,
				$moonChance
			);

			if ($planetId) {
				$GottenMoon = sprintf(__('fleet_engine.sys_moonbuilt'), $this->fleet->end_galaxy, $this->fleet->end_system, $this->fleet->end_planet);
			} else {
				$GottenMoon = 'Предпринята попытка образования луны, но данные координаты уже заняты другой луной';
			}
		} else {
			$GottenMoon = '';
		}

		// Очки военного опыта
		$warPoints 		= round($totalDebree / 25000);
		$AddWarPoints 	= ($result['won'] != 2) ? $warPoints : 0;
		// Сборка массива ID участников боя
		$FleetsUsers = [];

		$tmp = [];

		foreach ($attackUsers as $info) {
			if (!in_array($info['tech']['id'], $tmp)) {
				$tmp[] = $info['tech']['id'];
			}
		}

		$realAttackersUsers = count($tmp);
		unset($tmp);

		foreach ($attackUsers as $info) {
			if (!in_array($info['tech']['id'], $FleetsUsers)) {
				$FleetsUsers[] = (int) $info['tech']['id'];

				if ($this->fleet->mission != MissionEnum::Spy) {
					$update = ['raids' => DB::raw('raids + 1')];

					if ($result['won'] == 1) {
						$update['raids_win'] = DB::raw('raids_win + 1');
					} elseif ($result['won'] == 2) {
						$update['raids_lose'] =  DB::raw('raids_lose + 1');
					}

					if ($AddWarPoints > 0) {
						$update['xpraid'] = DB::raw('xpraid + ' . ceil($AddWarPoints / $realAttackersUsers));
					}

					Models\User::find($info['tech']['id'])->update($update);
				}
			}
		}
		foreach ($defenseUsers as $info) {
			if (!in_array($info['tech']['id'], $FleetsUsers)) {
				$FleetsUsers[] = (int) $info['tech']['id'];

				if ($this->fleet->mission != MissionEnum::Spy) {
					$update = ['raids' => DB::raw('raids + 1')];

					if ($result['won'] == 2) {
						$update['raids_win'] = DB::raw('raids_win + 1');
					} elseif ($result['won'] == 1) {
						$update['raids_lose'] = DB::raw('raids_lose + 1');
					}

					Models\User::query()->where('id', $info['tech']['id'])->update($update);
				}
			}
		}

		// Уничтожен в первой волне
		$no_contact = (count($result['rw']) <= 2 && $result['won'] == 2) ? 1 : 0;

		$report = Models\Report::create([
			'users_id' 		=> $FleetsUsers,
			'no_contact' 	=> $no_contact,
			'data' 			=> [$result, $attackUsers, $defenseUsers, $steal, $moonChance, $GottenMoon, $repairFleets],
		]);

		if ($this->fleet->assault) {
			$this->fleet->assault->delete();
		}

		$lost = $result['lost']['att'] + $result['lost']['def'];

		if ($lost >= config('game.hallPoints', 1000000)) {
			$sab = 0;

			$userList = [];

			foreach ($attackUsers as $info) {
				if (!in_array($info['username'], $userList)) {
					$userList[] = $info['username'];
				}
			}

			if (count($userList) > 1) {
				$sab = 1;
			}

			$title_1 = implode(',', $userList);

			$userList = [];

			foreach ($defenseUsers as $info) {
				if (!in_array($info['username'], $userList)) {
					$userList[] = $info['username'];
				}
			}

			if (count($userList) > 1) {
				$sab = 1;
			}

			$title_2 = implode(',', $userList);

			$title = $title_1 . ' vs ' . $title_2 . ' (П: ' . Format::number($lost) . ')';

			$battleLog = new Models\LogBattle();
			$battleLog->user_id = 0;
			$battleLog->title = $title;
			$battleLog->data = $report->data;

			if ($battleLog->save()) {
				Models\Hall::create([
					'title' 	=> $title,
					'debris' 	=> floor($lost / 1000),
					'time' 		=> now(),
					'won' 		=> $result['won'],
					'sab' 		=> $sab,
					'log' 		=> $battleLog->id
				]);
			}
		}

		$reportHtml = "<center>";
		$reportHtml .= '<a href="/rw/' . $report->id . '/' . md5(config('app.key') . $report->id) . '/" target="' . (config('game.view.openRaportInNewWindow', 0) == 1 ? '_blank' : '') . '">';
		$reportHtml .= "<font color=\"#COLOR#\">" . __('fleet_engine.sys_mess_attack_report') . " [" . $this->fleet->end_galaxy . ":" . $this->fleet->end_system . ":" . $this->fleet->end_planet . "]</font></a>";

		$report2Html  = '<br><br><font color=\'red\'>' . __('fleet_engine.sys_perte_attaquant') . ': ' . Format::number($result['lost']['att']) . '</font><font color=\'green\'>   ' . __('fleet_engine.sys_perte_defenseur') . ': ' . Format::number($result['lost']['def']) . '</font><br>';
		$report2Html .= __('fleet_engine.sys_gain') . ' м: <font color=\'#adaead\'>' . Format::number($steal['metal']) . '</font>, к: <font color=\'#ef51ef\'>' . Format::number($steal['crystal']) . '</font>, д: <font color=\'#f77542\'>' . Format::number($steal['deuterium']) . '</font><br>';
		$report2Html .= __('fleet_engine.sys_debris') . ' м: <font color=\'#adaead\'>' . Format::number($result['debree']['att'][0] + $result['debree']['def'][0]) . '</font>, к: <font color=\'#ef51ef\'>' . Format::number($result['debree']['att'][1] + $result['debree']['def'][1]) . '</font></center>';

		$userList = [];

		foreach ($attackUsers as $info) {
			if (!in_array($info['tech']['id'], $userList)) {
				$userList[] = $info['tech']['id'];
			}
		}

		$attackersReport = $reportHtml . $report2Html;

		$color = 'orange';

		if ($result['won'] == 1) {
			$color = 'green';
		} elseif ($result['won'] == 2) {
			$color = 'red';
		}

		$attackersReport = str_replace('#COLOR#', $color, $attackersReport);

		foreach ($userList as $userId) {
			User::find($userId)?->notify(new MessageNotification(null, MessageType::Battle, 'Боевой доклад', $attackersReport));
		}

		$userList = [];

		foreach ($defenseUsers as $info) {
			if (!in_array($info['tech']['id'], $userList)) {
				$userList[] = $info['tech']['id'];
			}
		}

		$defendersReport = $reportHtml;

		$color = 'orange';

		if ($result['won'] == 1) {
			$color = 'red';
		} elseif ($result['won'] == 2) {
			$color = 'green';
		}

		$defendersReport = str_replace('#COLOR#', $color, $defendersReport);

		foreach ($userList as $userId) {
			User::find($userId)?->notify(new MessageNotification(null, MessageType::Battle, 'Боевой доклад', $defendersReport));
		}

		LogAttack::create([
			'user_id' 		=> $this->fleet->user_id,
			'planet_start' 	=> 0,
			'planet_end'	=> $target->id,
			'fleet' 		=> $this->fleet->fleet_array,
			'battle_log'	=> $report->id
		]);

		return true;
	}

	public function endStayEvent()
	{
	}

	public function returnEvent()
	{
		$this->restoreFleetToPlanet();
		$this->killFleet();
	}

	public function getGroupFleet(FleetModel $fleet, PlayerGroup $playerGroup)
	{
		$fleetData = $fleet->getShips();

		if (!count($fleetData)) {
			if ($fleet->mission == MissionEnum::Attack || ($fleet->mission == MissionEnum::Assault && count($fleetData) == 1 && isset($fleetData[210]))) {
				$fleet->return();
			}

			return;
		}

		if (!isset($this->usersInfo[$fleet->user_id])) {
			$this->usersInfo[$fleet->user_id] = [];
		}

		$this->usersInfo[$fleet->user_id][$fleet->id] = [
			'galaxy' => $fleet->start_galaxy,
			'system' => $fleet->start_system,
			'planet' => $fleet->start_planet
		];

		$res = [];

		foreach ($fleetData as $shipId => $shipArr) {
			if (Vars::getItemType($shipId) != ItemType::FLEET) {
				continue;
			}

			$res[$shipId] = $shipArr['count'];
		}

		if (!isset($this->usersTech[$fleet->user_id])) {
			$user = User::query()->find($fleet->user_id);

			$playerObj = new Player($fleet->user_id);
			$playerObj->setName($user->username);
			$playerObj->setTech(0, 0, 0);

			$info = [
				'military_tech' => $user->getTechLevel('military'),
				'defence_tech' 	=> $user->getTechLevel('defence'),
				'shield_tech' 	=> $user->getTechLevel('shield'),
				'laser_tech' 	=> $user->getTechLevel('laser'),
				'ionic_tech' 	=> $user->getTechLevel('ionic'),
				'buster_tech' 	=> $user->getTechLevel('buster'),
			];

			if ($user->rpg_komandir?->isFuture()) {
				$info['military_tech'] 	+= 2;
				$info['defence_tech'] 	+= 2;
				$info['shield_tech'] 	+= 2;
			}

			foreach (Vars::getItemsByType(ItemType::TECH) as $techId) {
				if (isset($info[Vars::getName($techId)]) && $info[Vars::getName($techId)] > 0) {
					$res[$techId] = $info[Vars::getName($techId)];
				}
			}

			$this->usersTech[$fleet->user_id] = $res;
		} else {
			$playerObj = $playerGroup->getPlayer($fleet->user_id);

			if (!$playerObj) {
				$info = User::find($fleet->user_id);

				$playerObj = new Player($fleet->user_id);
				$playerObj->setName($info->username);
				$playerObj->setTech(0, 0, 0);
			}

			foreach ($this->usersTech[$fleet->user_id] as $rId => $rVal) {
				if (!isset($res[$rId])) {
					$res[$rId] = $rVal;
				}
			}
		}

		$fleetObj = new Fleet($fleet->id);

		foreach ($fleetData as $shipId => $shipArr) {
			if (Vars::getItemType($shipId) != ItemType::FLEET || !$shipArr['count']) {
				continue;
			}

			$fleetObj->addShipType($this->getShipType($shipId, $shipArr['count'], $res));
		}

		if (!$fleetObj->isEmpty()) {
			$playerObj->addFleet($fleetObj);
		}

		if (!$playerGroup->existPlayer($fleet->user_id)) {
			$playerGroup->addPlayer($playerObj);
		}
	}

	public function getShipType($id, $count, $res)
	{
		$shipData = Vars::getUnitData($id);

		$attDef 	= ($res[111] ?? 0) * 0.05;
		$attTech 	= ($res[109] ?? 0) * 0.05;

		if ($shipData['type_gun'] == 1) {
			$attTech += ($res[120] ?? 0) * 0.05;
		} elseif ($shipData['type_gun'] == 2) {
			$attTech += ($res[121] ?? 0) * 0.05;
		} elseif ($shipData['type_gun'] == 3) {
			$attTech += ($res[122] ?? 0) * 0.05;
		}

		$price = Vars::getItemPrice($id);

		$cost = [$price['metal'], $price['crystal']];

		if (Vars::getItemType($id) == ItemType::FLEET) {
			return new Ship($id, $count, $shipData['sd'], $shipData['shield'], $cost, $shipData['attack'], $attTech, (($res[110] ?? 0) * 0.05), $attDef);
		}

		return new Defense($id, $count, $shipData['sd'], $shipData['shield'], $cost, $shipData['attack'], $attTech, (($res[110] ?? 0) * 0.05), $attDef);
	}

	public function convertPlayerGroupToArray(PlayerGroup $_playerGroup)
	{
		$result = [];

		foreach ($_playerGroup as $_player) {
			/** @var Player $_player */
			$result[$_player->getId()] = [
				'username' 	=> $_player->getName(),
				'fleet' 	=> $this->usersInfo[$_player->getId()],
				'tech' 		=> [
					'id' 			=> $_player->getId(),
					'military_tech' => $this->usersTech[$_player->getId()][109] ?? 0,
					'shield_tech' 	=> $this->usersTech[$_player->getId()][110] ?? 0,
					'defence_tech' 	=> $this->usersTech[$_player->getId()][111] ?? 0,
					'laser_tech'	=> $this->usersTech[$_player->getId()][120] ?? 0,
					'ionic_tech'	=> $this->usersTech[$_player->getId()][121] ?? 0,
					'buster_tech'	=> $this->usersTech[$_player->getId()][122] ?? 0
				],
				'flvl' => $this->usersTech[$_player->getId()],
			];
		}

		return $result;
	}

	public function convertRoundToArray(Round $round)
	{
		$result = [
			'attackers' 	=> [],
			'defenders' 	=> [],
			'attack'		=> ['total' => $round->getAttackersFirePower()],
			'defense' 		=> ['total' => $round->getDefendersFirePower()],
			'attackA' 		=> ['total' => $round->getAttackersFireCount()],
			'defenseA' 		=> ['total' => $round->getDefendersFireCount()]
		];

		$attackers = $round->getAfterBattleAttackers();
		$defenders = $round->getAfterBattleDefenders();

		foreach ($attackers as $_player) {
			foreach ($_player as $_idFleet => $_fleet) {
				/**
				 * @var ShipType $_ship
				 */
				foreach ($_fleet as $_shipID => $_ship) {
					$result['attackers'][$_idFleet][$_shipID] = $_ship->getCount();

					if (!isset($result['attackA'][$_idFleet]['total'])) {
						$result['attackA'][$_idFleet]['total'] = 0;
					}

					$result['attackA'][$_idFleet]['total'] += $_ship->getCount();
				}
			}
		}

		foreach ($defenders as $_player) {
			foreach ($_player as $_idFleet => $_fleet) {
				/**
				 * @var ShipType $_ship
				 */
				foreach ($_fleet as $_shipID => $_ship) {
					$result['defenders'][$_idFleet][$_shipID] = $_ship->getCount();

					if (!isset($result['defenseA'][$_idFleet]['total'])) {
						$result['defenseA'][$_idFleet]['total'] = 0;
					}

					$result['defenseA'][$_idFleet]['total'] += $_ship->getCount();
				}
			}
		}

		$result['attackShield'] = $round->getAttachersAssorbedDamage();
		$result['defShield'] 	= $round->getDefendersAssorbedDamage();

		return $result;
	}

	public function getResultFleetArray(PlayerGroup $playerGroupBeforeBattle, PlayerGroup $playerGroupAfterBattle)
	{
		$result = [];

		foreach ($playerGroupBeforeBattle->getIterator() as $idPlayer => $player) {
			/**
			 * @var $player Player
			 * @var $Xplayer Player
			 */
			$existPlayer = $playerGroupAfterBattle->existPlayer($idPlayer);

			$Xplayer = null;

			if ($existPlayer) {
				$Xplayer = $playerGroupAfterBattle->getPlayer($idPlayer);
			}

			foreach ($player->getIterator() as $idFleet => $fleet) {
				/**
				 * @var $fleet Fleet
				 * @var $Xfleet Fleet
				 */
				$existFleet = $existPlayer && $Xplayer->existFleet($idFleet);
				$Xfleet = null;

				$result[$idFleet] = [];

				if ($existFleet) {
					$Xfleet = $Xplayer->getFleet($idFleet);
				}

				foreach ($fleet as $idShipType => $fighters) {
					$existShipType 	= $existFleet && $Xfleet->existShipType($idShipType);

					if ($existShipType) {
						$XshipType = $Xfleet->getShipType($idShipType);
						$result[$idFleet][$idShipType] = $XshipType->getCount();
					} else {
						$result[$idFleet][$idShipType] = 0;
					}
				}
			}
		}

		return $result;
	}

	private function getSteal(Planet $planet, $capacity = 0)
	{
		$steal = ['metal' => 0, 'crystal' => 0, 'deuterium' => 0];

		if ($capacity > 0) {
			$metal 		= $planet->metal / 2;
			$crystal 	= $planet->crystal / 2;
			$deuter 	= $planet->deuterium / 2;

			$steal['metal'] 	= min($capacity / 3, $metal);
			$capacity -= $steal['metal'];

			$steal['crystal'] 	= min($capacity / 2, $crystal);
			$capacity -= $steal['crystal'];

			$steal['deuterium'] = min($capacity, $deuter);
			$capacity -= $steal['deuterium'];

			if ($capacity > 0) {
				$oldStealMetal = $steal['metal'];

				$steal['metal'] += min(($capacity / 2), ($metal - $steal['metal']));
				$capacity -= $steal['metal'] - $oldStealMetal;

				$steal['crystal'] += min($capacity, ($crystal - $steal['crystal']));
			}
		}

		$steal['metal'] 	= max($steal['metal'], 0);
		$steal['crystal'] 	= max($steal['crystal'], 0);
		$steal['deuterium'] = max($steal['deuterium'], 0);

		return array_map('round', $steal);
	}
}
