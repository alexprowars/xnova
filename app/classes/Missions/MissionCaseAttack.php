<?php

namespace App\Missions;

use App\Battle\Core\Battle;
use App\Battle\Core\Round;
use App\Battle\LangImplementation;
use App\Battle\Models\Defense;
use App\Battle\Models\HomeFleet;
use App\Battle\Models\Player;
use App\Battle\Models\PlayerGroup;
use App\Battle\Models\Ship;
use App\Battle\Models\ShipType;
use App\Battle\Models\Fleet;
use App\Battle\Core;
use App\Battle\Utils\LangManager;
use App\Fleet as FleetMethods;
use App\FleetEngine;
use App\Helpers;
use App\Models\Planet;
use App\Models\User;

class MissionCaseAttack extends FleetEngine implements Mission
{
	public $usersTech = [];
	public $usersInfo = [];

	function __construct($Fleet)
	{
			$this->_fleet = $Fleet;
	}

	public function TargetEvent()
	{
		$target = Planet::findByCoords($this->_fleet['end_galaxy'], $this->_fleet['end_system'], $this->_fleet['end_planet'], $this->_fleet['end_type']);

		if (!isset($target->id) || !$target->id_owner || $target->destruyed > 0)
		{
			$this->ReturnFleet();

			return false;
		}

		$owner = User::findFirst($this->_fleet['owner']);

		if (!$owner)
		{
			$this->ReturnFleet();

			return false;
		}

		/**
		 * @var \App\Models\User $targetUser
		 */
		$targetUser = User::findFirst($target->id_owner);

		if (!isset($targetUser->id))
		{
			$this->ReturnFleet();

			return false;
		}

		$target->assignUser($targetUser);
		$target->PlanetResourceUpdate($this->_fleet['create_time']);

		LangManager::getInstance()->setImplementation(new LangImplementation());

		$attackers = new PlayerGroup();
		$defenders = new PlayerGroup();

		$this->getGroupFleet($this->_fleet, $attackers);

		if ($this->_fleet['group_id'] != 0)
		{
			$fleets = $this->db->query('SELECT * FROM game_fleets WHERE id != ' . $this->_fleet['id'] . ' AND group_id = ' . $this->_fleet['group_id']);

			while ($fleet = $fleets->fetch())
			{
				$this->getGroupFleet($fleet, $attackers);
			}
		}

		$def = $this->db->query('SELECT * FROM game_fleets WHERE `end_galaxy` = ' . $this->_fleet['end_galaxy'] . ' AND `end_system` = ' . $this->_fleet['end_system'] . ' AND `end_type` = ' . $this->_fleet['end_type'] . ' AND `end_planet` = ' . $this->_fleet['end_planet'] . ' AND mess = 3');

		while ($fleet = $def->fetch())
		{
			$this->getGroupFleet($fleet, $defenders);
		}

		$res = [];

		for ($i = 200; $i < 500; $i++)
		{
			if (isset($this->game->resource[$i]) && isset($target->{$this->game->resource[$i]}) && $target->{$this->game->resource[$i]} > 0)
			{
				$res[$i] = $target->{$this->game->resource[$i]};

				$l = $i > 400 ? ($i - 50) : ($i + 100);

				if (isset($this->game->resource[$l]) && isset($targetUser->{$this->game->resource[$l]}) && $targetUser->{$this->game->resource[$l]} > 0)
					$res[$l] = $targetUser->{$this->game->resource[$l]};
			}
		}

		if ($targetUser->rpg_komandir > time())
		{
			$targetUser->military_tech 	+= 2;
			$targetUser->defence_tech 	+= 2;
			$targetUser->shield_tech 	+= 2;
		}

		foreach ($this->game->reslist['tech'] AS $techId)
		{
			if (isset($targetUser->{$this->game->resource[$techId]}) && $targetUser->{$this->game->resource[$techId]} > 0)
				$res[$techId] = $targetUser->{$this->game->resource[$techId]};
		}

		$this->usersTech[$targetUser->id] = $res;

		$homeFleet = new HomeFleet(0);

		for ($i = 200; $i < 500; $i++)
		{
			if (isset($this->game->resource[$i]) && isset($target->{$this->game->resource[$i]}) && $target->{$this->game->resource[$i]} > 0)
			{
				$l = $i > 400 ? ($i - 50) : ($i + 100);

				$shipType = $this->getShipType($i, [$target->{$this->game->resource[$i]}, (isset($this->game->resource[$l]) && isset($targetUser->{$this->game->resource[$l]}) ? $targetUser->{$this->game->resource[$l]} : 0)], $res);

				if ($targetUser->rpg_ingenieur && $shipType->getType() == 'Ship')
					$shipType->setRepairProb(0.8);

				$homeFleet->addShipType($shipType);
			}
		}

		if (!$defenders->existPlayer($targetUser->id))
		{
			$player = new Player($targetUser->id, [$homeFleet]);
			$player->setTech(0, 0, 0);
			$player->setName($targetUser->username);
			$defenders->addPlayer($player);

			if (!isset($this->usersInfo[$targetUser->id]))
				$this->usersInfo[$targetUser->id] = [];

			$this->usersInfo[$targetUser->id][0] = [
				'galaxy' => $target->galaxy,
				'system' => $target->system,
				'planet' => $target->planet
			];
		}
		else
			$defenders->getPlayer($targetUser->id)->addDefense($homeFleet);

		if (!$this->_fleet['raunds'])
			$this->_fleet['raunds'] = 6;

		$engine = new Battle($attackers, $defenders, $this->_fleet['raunds']);
		$report = $engine->getReport();
		$result = ['version' => 2, 'time' => time(), 'rw' => []];

		$attackUsers 	= $this->convertPlayerGroupToArray($report->getResultAttackersFleetOnRound('START'));
		$defenseUsers 	= $this->convertPlayerGroupToArray($report->getResultDefendersFleetOnRound('START'));

		for ( $_i = 0; $_i <= $report->getLastRoundNumber(); $_i++)
		{
			$result['rw'][] = $this->convertRoundToArray($report->getRound($_i));
		}

		$result['won'] = 0;

		if ($report->attackerHasWin())
			$result['won'] = 1;
		if ($report->defenderHasWin())
			$result['won'] = 2;
		if ($report->isAdraw())
			$result['won'] = 0;

		$result['lost'] = ['att' => $report->getTotalAttackersLostUnits(), 'def' => $report->getTotalDefendersLostUnits()];

		$debris = $report->getDebris();

		$result['debree']['att'] = $debris;
		$result['debree']['def'] = [0, 0];

		$attackFleets 	= $this->getResultFleetArray($report->getPresentationAttackersFleetOnRound('START'), $report->getAfterBattleAttackers());
		$defenseFleets 	= $this->getResultFleetArray($report->getPresentationDefendersFleetOnRound('START'), $report->getAfterBattleDefenders());

		$repairFleets = [];

		foreach ($report->getDefendersRepaired() as $_player)
		{
			foreach ($_player as $_idFleet => $_fleet)
			{
				/**
				 * @var ShipType $_ship
				 */
				foreach($_fleet as $_shipID => $_ship)
				{
					$repairFleets[$_idFleet][$_shipID] = $_ship->getCount();
				}
			}
		}

		$fleetToUser = [];

		foreach ($report->getPresentationAttackersFleetOnRound('START') as $idPlayer => $player)
		{
			/**
			 * @var $player Player
			 */
			foreach ($player->getIterator() as $idFleet => $fleet)
			{
				$fleetToUser[$idFleet] = $idPlayer;
			}
		}

		$steal = ['metal' => 0, 'crystal' => 0, 'deuterium' => 0];

		if ($result['won'] == 1)
		{
			$max_resources = 0;
			$max_fleet_res = [];

			foreach ($attackFleets AS $fleet => $arr)
			{
				$max_fleet_res[$fleet] = 0;

				foreach ($arr as $Element => $amount)
				{
					if ($Element == 210)
						continue;

					if (isset($attackUsers[$fleetToUser[$fleet]]['flvl'][$Element]) && isset($this->game->CombatCaps[$Element]['power_consumption']) && $this->game->CombatCaps[$Element]['power_consumption'] > 0)
						$capacity = $this->game->CombatCaps[$Element]['capacity'] * $amount * (1 + $attackUsers[$fleetToUser[$fleet]]['flvl'][$Element] * ($this->game->CombatCaps[$Element]['power_consumption'] / 100));
					else
						$capacity = $this->game->CombatCaps[$Element]['capacity'] * $amount;

					$max_resources += $capacity;
					$max_fleet_res[$fleet] += $capacity;
				}
			}

			$res_correction = $max_resources;
			$res_procent = [];

			if ($max_resources > 0)
			{
				foreach ($max_fleet_res AS $id => $res)
				{
					$res_procent[$id] = $max_fleet_res[$id] / $res_correction;
				}
			}

			$steal = $this->getSteal($target->toArray(), $max_resources);
		}

		$totalDebree = $result['debree']['def'][0] + $result['debree']['def'][1] + $result['debree']['att'][0] + $result['debree']['att'][1];

		if ($totalDebree > 0)
		{
			$this->db->updateAsDict('game_planets',
			[
				'+debris_metal' 	=> ($result['debree']['att'][0] + $result['debree']['def'][0]),
				'+debris_crystal' 	=> ($result['debree']['att'][1] + $result['debree']['def'][1])
			],
			"galaxy = ".$target->galaxy." AND system = ".$target->system." AND planet = ".$target->planet." AND planet_type != 3");
		}
		
		foreach ($attackFleets as $fleetID => $attacker)
		{
			$fleetArray = '';
			$totalCount = 0;

			foreach ($attacker as $element => $amount)
			{
				if (!is_numeric($element) || !$amount)
					continue;

				$fleetArray .= $element . ',' . $amount . '!0;';
				$totalCount += $amount;
			}

			if ($totalCount <= 0)
				$this->KillFleet($fleetID);
			else
			{
				$update = [
					'fleet_array' 	=> substr($fleetArray, 0, -1),
					'@update' 		=> 'end_time',
					'mess'			=> 1,
					'group_id'		=> 0,
					'won'			=> $result['won']
				];

				if ($result['won'] == 1 && ($steal['metal'] > 0 || $steal['crystal'] > 0 || $steal['deuterium'] > 0))
				{
					if (isset($res_procent[$fleetID]))
					{
						$update['+resource_metal'] 		= round($res_procent[$fleetID] * $steal['metal']);
						$update['+resource_crystal'] 	= round($res_procent[$fleetID] * $steal['crystal']);
						$update['+resource_deuterium'] 	= round($res_procent[$fleetID] * $steal['deuterium']);
					}
				}

				$this->db->updateAsDict("game_fleets", $update, "id = ".$fleetID);
			}
		}

		foreach ($defenseFleets as $fleetID => $defender)
		{
			if ($fleetID != 0)
			{
				$fleetArray = '';
				$totalCount = 0;

				foreach ($defender as $element => $amount)
				{
					if (!is_numeric($element) || !$amount)
						continue;

					$fleetArray .= $element . ',' . $amount . '!0;';
					$totalCount += $amount;
				}

				if ($totalCount <= 0)
					$this->KillFleet($fleetID);
				else
				{
					$this->db->updateAsDict(
					[
						'fleet_array' => substr($fleetArray, 0, -1),
						'@update' => 'end_time'
					],
					"id = ".$fleetID);
				}
			}
			else
			{
				$arFields = [];

				if ($steal['metal'] > 0 || $steal['crystal'] > 0 || $steal['deuterium'] > 0)
				{
					$arFields = [
						'-metal' 		=> $steal['metal'],
						'-crystal' 		=> $steal['crystal'],
						'-deuterium' 	=> $steal['deuterium']
					];
				}

				for ($i = 200; $i < 500; $i++)
				{
					if (isset($this->game->resource[$i]) && isset($defender[$i]) && isset($target->{$this->game->resource[$i]}) && $defender[$i] != $target->{'~'.$this->game->resource[$i]})
						$arFields[$this->game->resource[$i]] = $defender[$i];
				}

				if (count($arFields) > 0)
					$target->saveData($arFields);
			}
		}
		
		$moonChance = $report->getMoonProb();

		if ($target->planet_type != 1)
			$moonChance = 0;

		$userChance = mt_rand(1, 100);

		if ($this->_fleet['end_type'] == 5)
			$userChance = 0;

		if ($target->parent_planet == 0 && $userChance && $userChance <= $moonChance)
		{
			$TargetPlanetName = $target->createMoon($this->_fleet['end_galaxy'], $this->_fleet['end_system'], $this->_fleet['end_planet'], $target->id_owner, $moonChance);

			if ($TargetPlanetName)
				$GottenMoon = sprintf(_getText('sys_moonbuilt'), $this->_fleet['end_galaxy'], $this->_fleet['end_system'], $this->_fleet['end_planet']);
			else
				$GottenMoon = 'Предпринята попытка образования луны, но данные координаты уже заняты другой луной';
		}
		else
			$GottenMoon = '';

		// Очки военного опыта
		$warPoints 		= round($totalDebree / 25000);
		$AddWarPoints 	= ($result['won'] != 2) ? $warPoints : 0;
		// Сборка массива ID участников боя
		$FleetsUsers = [];

		$tmp = [];

		foreach ($attackUsers AS $info)
		{
			if (!in_array($info['tech']['id'], $tmp))
			{
				$tmp[] = $info['tech']['id'];
			}
		}

		$realAttackersUsers = count($tmp);
		unset($tmp);

		foreach ($attackUsers AS $info)
		{
			if (!in_array($info['tech']['id'], $FleetsUsers))
			{
				$FleetsUsers[] = (int) $info['tech']['id'];

				if ($this->_fleet['mission'] != 6)
				{
					$update = ['+raids' => 1];

					if ($result['won'] == 1)
						$update['+raids_win'] = 1;
					elseif ($result['won'] == 2)
						$update['+raids_lose'] = 1;

					if ($AddWarPoints > 0)
						$update['+xpraid'] = ceil($AddWarPoints / $realAttackersUsers);

					$this->db->updateAsDict('game_users', $update, "id = ".$info['tech']['id']);
				}
			}
		}
		foreach ($defenseUsers AS $info)
		{
			if (!in_array($info['tech']['id'], $FleetsUsers))
			{
				$FleetsUsers[] = (int) $info['tech']['id'];

				if ($this->_fleet['mission'] != 6)
				{
					$update = ['+raids' => 1];

					if ($result['won'] == 2)
						$update['+raids_win'] = 1;
					elseif ($result['won'] == 1)
						$update['+raids_lose'] = 1;

					$this->db->updateAsDict('game_users', $update, "id = ".$info['tech']['id']);
				}
			}
		}

		// Упаковка в строку
		$users = json_encode($FleetsUsers);
		// Упаковка в строку
		$raport = json_encode([$result, $attackUsers, $defenseUsers, $steal, $moonChance, $GottenMoon, $repairFleets]);
		// Уничтожен в первой волне
		$no_contact = (count($result['rw']) <= 2 && $result['won'] == 2) ? 1 : 0;
		// Добавление в базу
		$this->db->insertAsDict('game_rw',
		[
			'time' 			=> time(),
			'id_users' 		=> $users,
			'no_contact' 	=> $no_contact,
			'raport' 		=> $raport,
		]);
		// Ключи авторизации доклада
		$ids = $this->db->lastInsertId();

		if ($this->_fleet['group_id'] != 0)
		{
			$this->db->delete('game_aks', 'id = ?', [$this->_fleet['group_id']]);
			$this->db->delete('game_aks_user', 'aks_id = ?', [$this->_fleet['group_id']]);
		}

		$lost = $result['lost']['att'] + $result['lost']['def'];

		if ($lost >= $this->config->game->get('hallPoints', 1000000))
		{
			$sab = 0;

			$UserList = [];

			foreach ($attackUsers AS $info)
			{
				if (!in_array($info['username'], $UserList))
					$UserList[] = $info['username'];
			}

			if (count($UserList) > 1)
				$sab = 1;

			$title_1 = implode(',', $UserList);

			$UserList = [];

			foreach ($defenseUsers AS $info)
			{
				if (!in_array($info['username'], $UserList))
					$UserList[] = $info['username'];
			}

			if (count($UserList) > 1)
				$sab = 1;

			$title_2 = implode(',', $UserList);

			$title = '' . $title_1 . ' vs ' . $title_2 . ' (П: ' . Helpers::pretty_number($lost) . ')';

			$this->db->insertAsDict('game_savelog',
			[
				'user' 	=> 0,
				'title' => $title,
				'log' 	=> $raport
			]);

			$id = $this->db->lastInsertId();

			$this->db->insertAsDict('game_hall',
			[
				'title' 	=> $title,
				'debris' 	=> floor($lost / 1000),
				'time' 		=> time(),
				'won' 		=> $result['won'],
				'sab' 		=> $sab,
				'log' 		=> $id
			]);
		}

		$raport = "<center><a ".($this->config->view->get('openRaportInNewWindow', 0) == 1 ? 'target="_blank"' : '')." href=\"/rw/" . $ids . "/" . md5('xnovasuka' . $ids) . "/\">";

		if ($result['won'] == 1)
			$raport .= "<font color=\"green\">";
		elseif ($result['won'] == 0)
			$raport .= "<font color=\"orange\">";
		elseif ($result['won'] == 2)
			$raport .= "<font color=\"red\">";

		$raport .= _getText('sys_mess_attack_report') . " [" . $this->_fleet['end_galaxy'] . ":" . $this->_fleet['end_system'] . ":" . $this->_fleet['end_planet'] . "]</font></a>";

		$raport2  = $raport . '<br><br><font color=\'red\'>' . _getText('sys_perte_attaquant') . ': ' . Helpers::pretty_number($result['lost']['att']) . '</font><font color=\'green\'>   ' . _getText('sys_perte_defenseur') . ': ' . Helpers::pretty_number($result['lost']['def']) . '</font><br>';
		$raport2 .= _getText('sys_gain') . ' м: <font color=\'#adaead\'>' . Helpers::pretty_number($steal['metal']) . '</font>, к: <font color=\'#ef51ef\'>' . Helpers::pretty_number($steal['crystal']) . '</font>, д: <font color=\'#f77542\'>' . Helpers::pretty_number($steal['deuterium']) . '</font><br>';
		$raport2 .= _getText('sys_debris') . ' м: <font color=\'#adaead\'>' . Helpers::pretty_number($result['debree']['att'][0] + $result['debree']['def'][0]) . '</font>, к: <font color=\'#ef51ef\'>' . Helpers::pretty_number($result['debree']['att'][1] + $result['debree']['def'][1]) . '</font></center>';

		$UserList = [];

		foreach ($attackUsers AS $info)
		{
			if (!in_array($info['tech']['id'], $UserList))
				$UserList[] = $info['tech']['id'];
		}

		foreach ($UserList AS $info)
			$this->game->sendMessage($info, 0, time(), 3, 'Боевой доклад', $raport2);

		$UserList = [];

		foreach ($defenseUsers AS $info)
		{
			if (!in_array($info['tech']['id'], $UserList))
				$UserList[] = $info['tech']['id'];
		}

		foreach ($UserList AS $info)
			$this->game->sendMessage($info, 0, time(), 3, 'Боевой доклад', $raport);

		$this->db->insertAsDict('game_log_attack',
		[
			'uid' 			=> $this->_fleet['owner'],
			'time'			=> time(),
			'planet_start' 	=> 0,
			'planet_end'	=> $target->id,
			'fleet' 		=> $this->_fleet['fleet_array'],
			'battle_log'	=> $ids
		]);

		return true;
	}

	public function EndStayEvent()
	{
		return;
	}

	public function ReturnEvent()
	{
		$this->RestoreFleetToPlanet();
		$this->KillFleet();
	}

	public function getGroupFleet ($fleet, PlayerGroup $playerGroup)
	{
		$fleetData = FleetMethods::unserializeFleet($fleet['fleet_array']);

		if (!count($fleetData))
		{
			if ($fleet['mission'] == 1 || ($fleet['mission'] == 2 && count($fleetData) == 1 && isset($fleetData[210])))
				$this->ReturnFleet([], $fleet['id']);

			return;
		}

		if (!isset($this->usersInfo[$fleet['owner']]))
			$this->usersInfo[$fleet['owner']] = [];

		$this->usersInfo[$fleet['owner']][$fleet['id']] = [
			'galaxy' => $fleet['start_galaxy'],
			'system' => $fleet['start_system'],
			'planet' => $fleet['start_planet']
		];

		$res = [];

		foreach ($fleetData as $shipId => $shipArr)
		{
			if ($shipId < 100 || $shipId > 300)
				continue;

			$res[$shipId] = $shipArr['cnt'];
			$res[$shipId + 100] = $shipArr['lvl'];
		}

		if (!isset($this->usersTech[$fleet['owner']]))
		{
			$info = $this->db->query('SELECT `id`, `username`, `military_tech`, `defence_tech`, `shield_tech`, `laser_tech`, `ionic_tech`, `buster_tech`, `rpg_admiral`, `rpg_komandir` FROM game_users WHERE id = ' . $fleet['owner'])->fetch();

			$playerObj = new Player($fleet['owner']);
			$playerObj->setName($info['username']);
			$playerObj->setTech(0, 0, 0);

			if ($info['rpg_komandir'] > time())
			{
				$info['military_tech'] 	+= 2;
				$info['defence_tech'] 	+= 2;
				$info['shield_tech'] 	+= 2;
			}

			foreach ($this->game->reslist['tech'] AS $techId)
			{
				if (isset($info[$this->game->resource[$techId]]) && $info[$this->game->resource[$techId]] > 0)
					$res[$techId] = $info[$this->game->resource[$techId]];
			}

			$this->usersTech[$fleet['owner']] = $res;
		}
		else
		{
			$playerObj = $playerGroup->getPlayer($fleet['owner']);

			if ($playerObj === false)
			{
				$info = $this->db->query('SELECT `id`, `username` FROM game_users WHERE id = ' . $fleet['owner'])->fetch();

				$playerObj = new Player($fleet['owner']);
				$playerObj->setName($info['username']);
				$playerObj->setTech(0, 0, 0);
			}
			
			foreach ($res AS $shipId => $lvl)
			{
				if ($shipId < 300 || $shipId > 400)
					continue;
			
				if (!isset($this->usersTech[$fleet['owner']][$shipId]))
				{
					$this->usersTech[$fleet['owner']][$shipId] = $lvl;
				}
			}

			foreach ($this->usersTech[$fleet['owner']] AS $rId => $rVal)
			{
				if (!isset($res[$rId]))
					$res[$rId] = $rVal;
			}
		}

		$fleetObj = new Fleet($fleet['id']);

		foreach ($fleetData as $shipId => $shipArr)
		{
			if ($shipId < 100 || $shipId > 300 || !$shipArr['cnt'])
				continue;

			$fleetObj->addShipType($this->getShipType($shipId, [$shipArr['cnt'], $shipArr['lvl']], $res));
		}

		if (!$fleetObj->isEmpty())
			$playerObj->addFleet($fleetObj);

		if (!$playerGroup->existPlayer($fleet['owner']))
			$playerGroup->addPlayer($playerObj);
	}

	public function getShipType($id, $count, $res)
	{
		$attDef 	= ($count[1] * ($this->game->CombatCaps[$id]['power_armour'] / 100)) + (isset($res[111]) ? $res[111] : 0) * 0.05;
		$attTech 	= (isset($res[109]) ? $res[109] : 0) * 0.05 + ($count[1] * ($this->game->CombatCaps[$id]['power_up'] / 100));

		if ($this->game->CombatCaps[$id]['type_gun'] == 1)
			$attTech += (isset($res[120]) ? $res[120] : 0) * 0.05;
		elseif ($this->game->CombatCaps[$id]['type_gun'] == 2)
			$attTech += (isset($res[121]) ? $res[121] : 0) * 0.05;
		elseif ($this->game->CombatCaps[$id]['type_gun'] == 3)
			$attTech += (isset($res[122]) ? $res[122] : 0) * 0.05;

		$cost = [$this->game->pricelist[$id]['metal'], $this->game->pricelist[$id]['crystal']];

		if (in_array($id, $this->game->reslist['fleet']))
			return new Ship($id, $count[0], $this->game->CombatCaps[$id]['sd'], $this->game->CombatCaps[$id]['shield'], $cost, $this->game->CombatCaps[$id]['attack'], $attTech, ((isset($res[110]) ? $res[110] : 0) * 0.05), $attDef);

		return new Defense($id, $count[0], $this->game->CombatCaps[$id]['sd'], $this->game->CombatCaps[$id]['shield'], $cost, $this->game->CombatCaps[$id]['attack'], $attTech, ((isset($res[110]) ? $res[110] : 0) * 0.05), $attDef);
	}

	public function convertPlayerGroupToArray (PlayerGroup $_playerGroup)
	{
		$result = [];

		foreach ($_playerGroup as $_player)
		{
			/**
			 * @var Player $_player
			 */
			$result[$_player->getId()] = [
				'username' 	=> $_player->getName(),
				'fleet' 	=> $this->usersInfo[$_player->getId()],
				'tech' 		=>
				[
					'id' 			=> $_player->getId(),
					'military_tech' => isset($this->usersTech[$_player->getId()][109]) ? $this->usersTech[$_player->getId()][109] : 0,
					'shield_tech' 	=> isset($this->usersTech[$_player->getId()][110]) ? $this->usersTech[$_player->getId()][110] : 0,
					'defence_tech' 	=> isset($this->usersTech[$_player->getId()][111]) ? $this->usersTech[$_player->getId()][111] : 0,
					'laser_tech'	=> isset($this->usersTech[$_player->getId()][120]) ? $this->usersTech[$_player->getId()][120] : 0,
					'ionic_tech'	=> isset($this->usersTech[$_player->getId()][121]) ? $this->usersTech[$_player->getId()][121] : 0,
					'buster_tech'	=> isset($this->usersTech[$_player->getId()][122]) ? $this->usersTech[$_player->getId()][122] : 0
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

		foreach ($attackers as $_player)
		{
			foreach ($_player as $_idFleet => $_fleet)
			{
				/**
				 * @var ShipType $_ship
				 */
				foreach($_fleet as $_shipID => $_ship)
				{
					$result['attackers'][$_idFleet][$_shipID] = $_ship->getCount();

					if (!isset($result['attackA'][$_idFleet]['total']))
						$result['attackA'][$_idFleet]['total'] = 0;

					$result['attackA'][$_idFleet]['total'] += $_ship->getCount();
				}
			}
		}

		foreach ($defenders as $_player)
		{
			foreach ($_player as $_idFleet => $_fleet)
			{
				/**
				 * @var ShipType $_ship
				 */
				foreach($_fleet as $_shipID => $_ship)
				{
					$result['defenders'][$_idFleet][$_shipID] = $_ship->getCount();

					if (!isset($result['defenseA'][$_idFleet]['total']))
						$result['defenseA'][$_idFleet]['total'] = 0;

					$result['defenseA'][$_idFleet]['total'] += $_ship->getCount();
				}
			}
		}

		$result['attackShield'] = $round->getAttachersAssorbedDamage();
		$result['defShield'] 	= $round->getDefendersAssorbedDamage();

		return $result;
	}

	public function getResultFleetArray (PlayerGroup $playerGroupBeforeBattle, PlayerGroup $playerGroupAfterBattle)
	{
		$result = [];

		foreach ($playerGroupBeforeBattle->getIterator() as $idPlayer => $player)
		{
			/**
			 * @var $player Player
			 * @var $Xplayer Player
			 */
			$existPlayer = $playerGroupAfterBattle->existPlayer($idPlayer);

			$Xplayer = null;

			if ($existPlayer)
				$Xplayer = $playerGroupAfterBattle->getPlayer($idPlayer);

			foreach ($player->getIterator() as $idFleet => $fleet)
			{
				/**
				 * @var $fleet Fleet
				 * @var $Xfleet Fleet
				 */
				$existFleet = $existPlayer && $Xplayer->existFleet($idFleet);
				$Xfleet = null;

				$result[$idFleet] = [];

				if ($existFleet)
					$Xfleet = $Xplayer->getFleet($idFleet);

				foreach ($fleet as $idShipType => $fighters)
				{
					$existShipType 	= $existFleet && $Xfleet->existShipType($idShipType);

					if ($existShipType)
					{
						$XshipType = $Xfleet->getShipType($idShipType);
						/**
						 * @var $XshipType ShipType
						 */
						$result[$idFleet][$idShipType] = $XshipType->getCount();
					}
					else
						$result[$idFleet][$idShipType] = 0;
				}
			}
		}

		return $result;
	}

	private function getSteal ($planet, $capacity = 0)
	{
		$steal = ['metal' => 0, 'crystal' => 0, 'deuterium' => 0];

		if ($capacity > 0)
		{
			$metal 		= $planet['metal'] / 2;
			$crystal 	= $planet['crystal'] / 2;
			$deuter 	= $planet['deuterium'] / 2;

			$steal['metal'] 	= min($capacity / 3, $metal);
			$capacity -= $steal['metal'];

			$steal['crystal'] 	= min($capacity / 2, $crystal);
			$capacity -= $steal['crystal'];

			$steal['deuterium'] = min($capacity, $deuter);
			$capacity -= $steal['deuterium'];

			if ($capacity > 0)
			{
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

?>