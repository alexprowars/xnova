<?php
namespace App\Missions;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use App\Battle\Core\Battle;
use App\Battle\LangImplementation;
use App\Battle\Models\Player;
use App\Battle\Models\PlayerGroup;
use App\Battle\Models\Fleet;
use App\Battle\Utils\LangManager;
use App\FleetEngine;
use App\Helpers;
use App\Models\User;

class MissionCaseExpedition extends FleetEngine implements Mission
{
	public function TargetEvent()
	{
		$this->StayFleet();
	}

	public function EndStayEvent()
	{
		$Expowert = [];

		foreach ($this->storage->reslist['fleet'] as $ID)
			$Expowert[$ID] = ($this->storage->pricelist[$ID]['metal'] + $this->storage->pricelist[$ID]['crystal']) / 200;

		$FleetPoints = 0;

		$FleetCapacity = 0;
		$FleetCount = [];

		foreach ($this->_fleet->getShips() as $type => $ship)
		{
			$FleetCount[$type] = $ship['cnt'];

			$FleetCapacity += $ship['cnt'] * $this->storage->CombatCaps[$type]['capacity'];

			$FleetPoints += $ship['cnt'] * $Expowert[$type];
		}

		$StatFactor = $this->db->fetchColumn("SELECT MAX(total_points) as total FROM game_statpoints WHERE stat_type = 1");

		if ($StatFactor < 10000)
			$upperLimit = 200;
		elseif ($StatFactor < 100000)
			$upperLimit = 2400;
		elseif ($StatFactor < 1000000)
			$upperLimit = 6000;
		elseif ($StatFactor < 5000000)
			$upperLimit = 9000;
		else
			$upperLimit = 12000;

		$FleetCapacity -= $this->_fleet->resource_metal + $this->_fleet->resource_crystal + $this->_fleet->resource_deuterium;
		$GetEvent = mt_rand(1, 10);

		switch ($GetEvent)
		{
			case 1:

				$WitchFound = mt_rand(1, 3);
				$FindSize = mt_rand(0, 100);

				if (10 < $FindSize)
				{
					$Factor = (mt_rand(10, 50) / $WitchFound) *  (1 + ($this->config->game->get('resource_multiplier') - 1) / 10);
					$Message = _getText('sys_expe_found_ress_1_' . mt_rand(1, 4));
				}
				elseif (0 < $FindSize && 10 >= $FindSize)
				{
					$Factor = (mt_rand(50, 100) / $WitchFound) * (1 + ($this->config->game->get('resource_multiplier') - 1) / 10);
					$Message = _getText('sys_expe_found_ress_2_' . mt_rand(1, 3));
				}
				else
				{
					$Factor = (mt_rand(100, 200) / $WitchFound) * (1 + ($this->config->game->get('resource_multiplier') - 1) / 10);
					$Message = _getText('sys_expe_found_ress_3_' . mt_rand(1, 2));
				}

				$Size = min($Factor * max(min($FleetPoints, $upperLimit), 200), $FleetCapacity);

				$this->_fleet->update_time = $this->_fleet->end_time;
				$this->_fleet->mess = 1;

				switch ($WitchFound)
				{
					case 1:
						$this->_fleet->resource_metal += $Size;
						break;
					case 2:
						$this->_fleet->resource_crystal += $Size;
						break;
					case 3:
						$this->_fleet->resource_deuterium += $Size;
						break;
				}

				$this->_fleet->update();

				break;

			case 2:

				$FindSize = mt_rand(0, 100);
				if(10 < $FindSize) {
					$Size		= mt_rand(1, 2);
				} elseif(0 < $FindSize && 10 >= $FindSize) {
					$Size		= mt_rand(2, 5);
				} else {
					$Size	 	= mt_rand(5, 10);
				}

				$Message = _getText('sys_expe_found_dm_1_'.mt_rand(1,5));

				$this->db->updateAsDict('game_users', ['+credits' => $Size], "id = ".$this->_fleet->owner);

				$this->_fleet->update_time = $this->_fleet->end_time;
				$this->_fleet->mess = 1;
				$this->_fleet->update();

				break;

			case 3:

				$FindSize = mt_rand(0, 100);
				if (10 < $FindSize)
				{
					$Size = mt_rand(10, 50);
					$Message = _getText('sys_expe_found_ships_1_' . mt_rand(1, 4));
				}
				elseif (0 < $FindSize && 10 >= $FindSize)
				{
					$Size = mt_rand(52, 100);
					$Message = _getText('sys_expe_found_ships_2_' . mt_rand(1, 2));
				}
				else
				{
					$Size = mt_rand(102, 200);
					$Message = _getText('sys_expe_found_ships_3_' . mt_rand(1, 2));
				}

				$FoundShips = max(round($Size * min($FleetPoints, ($upperLimit / 2))), 10000);

				$FoundShipMess = "";
				$NewFleetArray = "";

				$Found = [];

				foreach ($this->storage->reslist['fleet'] as $ID)
				{
					if(!isset($FleetCount[$ID]) || $ID == 208 || $ID == 209 || $ID == 214)
						continue;

					$MaxFound = floor($FoundShips / ($this->storage->pricelist[$ID]['metal'] + $this->storage->pricelist[$ID]['crystal']));

					if ($MaxFound <= 0)
						continue;

					$Count = mt_rand(0, $MaxFound);

					if ($Count <= 0)
						continue;

					$Found[$ID]	= $Count;

					$FoundShips	 		-= $Count * ($this->storage->pricelist[$ID]['metal'] + $this->storage->pricelist[$ID]['crystal']);
					$FoundShipMess   	.= '<br>'._getText('tech', $ID).': '.Helpers::pretty_number($Count);

					if ($FoundShips <= 0)
						break;
				}

				foreach ($FleetCount as $ID => $Count)
					$NewFleetArray .= $ID.",".($Count + (isset($Found[$ID]) ? floor($Found[$ID]) : 0))."!0;";

				$Message .= $FoundShipMess;

				$this->_fleet->update([
					'fleet_array' 	=> $NewFleetArray,
					'update_time' 	=> $this->_fleet->end_time,
					'mess' 			=> 1
				]);

				break;

			case 4:

				$Chance = mt_rand(1, 2);

				if ($Chance == 1)
				{
					$Points = [-3, -5, -8];
					$Which = 1;
					$Def = -3;
					$Name = _getText('sys_expe_attackname_1');
					$Add = 0;
					$Rand = [5, 3, 2];
					$defenderFleetArray = "204,5!0;206,3!0;207,2!0;";
				}
				else
				{
					$Points = [-4, -6, -9];
					$Which = 2;
					$Def = 3;
					$Name = _getText('sys_expe_attackname_2');
					$Add = 0.1;
					$Rand = [4, 3, 2];
					$defenderFleetArray = "205,5!0;207,5!0;213,2!0;";
				}

				$FindSize = mt_rand(0, 100);

				if (10 < $FindSize)
				{
					$Message = _getText('sys_expe_attack_' . $Which . '_1_' . $Rand[0]);
					$MaxAttackerPoints = 0.3 + $Add + (mt_rand($Points[0], abs($Points[0])) * 0.01);
				}
				elseif (0 < $FindSize && 10 >= $FindSize)
				{
					$Message = _getText('sys_expe_attack_' . $Which . '_2_' . $Rand[1]);
					$MaxAttackerPoints = 0.3 + $Add + (mt_rand($Points[1], abs($Points[1])) * 0.01);
				}
				else
				{
					$Message = _getText('sys_expe_attack_' . $Which . '_3_' . $Rand[2]);
					$MaxAttackerPoints = 0.3 + $Add + (mt_rand($Points[2], abs($Points[2])) * 0.01);
				}

				foreach ($FleetCount as $ID => $count)
				{
					$defenderFleetArray .= $ID . "," . round($count * $MaxAttackerPoints) . "!0;";
				}

				LangManager::getInstance()->setImplementation(new LangImplementation());

				$mission = new MissionCaseAttack(new \App\Models\Fleet());

				$attackers = new PlayerGroup();
				$defenders = new PlayerGroup();

				$mission->getGroupFleet($this->_fleet, $attackers);

				$fleetData = $this->_fleet->getShips($defenderFleetArray);

				$mission->usersInfo[0] = [];
				$mission->usersInfo[0][0] = [
					'galaxy' => $this->_fleet->end_galaxy,
					'system' => $this->_fleet->end_system,
					'planet' => $this->_fleet->end_planet
				];

				$res = [];

				foreach ($fleetData as $shipId => $shipArr)
				{
					if ($shipId < 100 || $shipId > 300)
						continue;

					$res[$shipId] = $shipArr['cnt'];
					$res[$shipId + 100] = $shipArr['lvl'];
				}

				$playerObj = new Player(0);
				$playerObj->setName($Name);
				$playerObj->setTech(0, 0, 0);

				foreach ($this->storage->reslist['tech'] AS $techId)
				{
					if (isset($mission->usersTech[$this->_fleet->owner][$this->storage->resource[$techId]]) && $mission->usersTech[$this->_fleet->owner][$this->storage->resource[$techId]] > 0)
						$res[$techId] = mt_rand(abs($mission->usersTech[$this->_fleet->owner][$this->storage->resource[$techId]] + $Def), 0);
				}

				$mission->usersTech[0] = $res;

				$fleetObj = new Fleet(0);

				foreach ($fleetData as $shipId => $shipArr)
				{
					if ($shipId < 100 || $shipId > 300 || !$shipArr['cnt'])
						continue;

					$fleetObj->addShipType($mission->getShipType($shipId, [$shipArr['cnt'], $shipArr['lvl']], $res));
				}

				if (!$fleetObj->isEmpty())
					$playerObj->addFleet($fleetObj);

				$defenders->addPlayer($playerObj);

				$this->config->game->offsetSet('repairDefenceFactor', 0);
				$this->config->game->offsetSet('battleRounds', 6);

				$engine = new Battle($attackers, $defenders);

				$report = $engine->getReport();

				$result = ['version' => 2, 'time' => time(), 'rw' => []];

				$attackUsers 	= $mission->convertPlayerGroupToArray($report->getResultAttackersFleetOnRound('START'));
				$defenseUsers 	= $mission->convertPlayerGroupToArray($report->getResultDefendersFleetOnRound('START'));

				for ( $_i = 0; $_i <= $report->getLastRoundNumber(); $_i++)
				{
					$result['rw'][] = $mission->convertRoundToArray($report->getRound($_i));
				}

				if ($report->attackerHasWin())
					$result['won'] = 1;
				if ($report->defenderHasWin())
					$result['won'] = 2;
				if ($report->isAdraw())
					$result['won'] = 0;

				$result['lost'] = ['att' => $report->getTotalAttackersLostUnits(), 'def' => $report->getTotalDefendersLostUnits()];

				$result['debree']['att'] = [0,0];
				$result['debree']['def'] = [0,0];

				$attackFleets = $mission->getResultFleetArray($report->getPresentationAttackersFleetOnRound('START'), $report->getAfterBattleAttackers());

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
						$this->db->updateAsDict($this->_fleet->getSource(),
						[
							'fleet_array' 	=> substr($fleetArray, 0, -1),
							'@update_time' 	=> 'end_time',
							'mess'			=> 1,
							'won'			=> $result['won']
						], "id = ".$fleetID);
					}
				}

				$FleetsUsers = [];

				foreach ($attackUsers AS $info)
				{
					$FleetsUsers[] = $info['tech']['id'];
				}

				// Упаковка в строку
				$raport = json_encode([$result, $attackUsers, $defenseUsers, array('metal' => 0, 'crystal' => 0, 'deuterium' => 0), 0, '', []]);
				// Добавление в базу
				$this->db->query("INSERT INTO game_rw SET time = " . time() . ", id_users = '" . json_encode($FleetsUsers) . "', no_contact = '0', raport = '" . addslashes($raport) . "';");
				// Ключи авторизации доклада
				$ids = $this->db->lastInsertId();

				$ColorAtt = $ColorDef = '';

				switch ($result['won'])
				{
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
				$MessageAtt = sprintf('<a href="#BASEPATH#rw/%s/%s/" target="_blank"><center><font color="%s">%s %s</font></a><br><br><font color="%s">%s: %s</font> <font color="%s">%s: %s</font><br>%s %s:<font color="#adaead">%s</font> %s:<font color="#ef51ef">%s</font> %s:<font color="#f77542">%s</font><br>%s %s:<font color="#adaead">%s</font> %s:<font color="#ef51ef">%s</font><br></center>', $ids, md5('xnovasuka' . $ids), $ColorAtt, 'Боевой доклад', sprintf(_getText('sys_adress_planet'), $this->_fleet->end_galaxy, $this->_fleet->end_system, $this->_fleet->end_planet), $ColorAtt, _getText('sys_perte_attaquant'), Helpers::pretty_number($result['lost']['att']), $ColorDef, _getText('sys_perte_defenseur'), Helpers::pretty_number($result['lost']['def']), _getText('sys_gain'), _getText('Metal'), 0, _getText('Crystal'), 0, _getText('Deuterium'), 0, _getText('sys_debris'), _getText('Metal'), 0, _getText('Crystal'), 0);

				User::sendMessage($this->_fleet->owner, 0, $this->_fleet->start_time, 3, _getText('sys_mess_tower'), $MessageAtt);

				break;

			case 5:

				$this->KillFleet();

				$Message = _getText('sys_expe_lost_fleet_' . mt_rand(1, 4));

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

				if ($MoreTime < 75)
				{
					$this->_fleet->end_time += ($this->_fleet->end_stay - $this->_fleet->start_time) * (array_rand($Wrapper) - 1);

					$Message = _getText('sys_expe_time_slow_'.mt_rand(1,6));
				}
				else
				{
					$this->_fleet->end_time -= max(1, (($this->_fleet->end_stay - $this->_fleet->start_time) / 3 * array_rand($Wrapper)));

					$Message = _getText('sys_expe_time_fast_'.mt_rand(1,3));
				}

				$this->_fleet->update([
					'update_time' 	=> $this->_fleet->end_time,
					'mess' 			=> 1
				]);

           		break;

			default:

				$this->ReturnFleet();

				$Message = _getText('sys_expe_nothing_' . mt_rand(1, 8));
		}

		User::sendMessage($this->_fleet->owner, 0, $this->_fleet->end_stay, 15, _getText('sys_expe_report'), $Message);
	}

	public function ReturnEvent()
	{
		$Message = sprintf(_getText('sys_expe_back_home'), _getText('Metal'), Helpers::pretty_number($this->_fleet->resource_metal), _getText('Crystal'), Helpers::pretty_number($this->_fleet->resource_crystal),  _getText('Deuterium'), Helpers::pretty_number($this->_fleet->resource_deuterium));

		User::sendMessage($this->_fleet->owner, 0, $this->_fleet->end_time, 15, _getText('sys_expe_report'), $Message);

		$this->RestoreFleetToPlanet();
		$this->KillFleet();
	}
}