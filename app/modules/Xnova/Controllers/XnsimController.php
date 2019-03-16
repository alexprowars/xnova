<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Battle\Core\Battle;
use Xnova\Battle\Core\Round;
use Xnova\Battle\Models\Defense;
use Xnova\Battle\Models\Fleet;
use Xnova\Battle\Models\Player;
use Xnova\Battle\Models\PlayerGroup;
use Xnova\Battle\Models\Ship;
use Xnova\Battle\Models\ShipType;
use Xnova\CombatReport;
use Phalcon\Mvc\View;
use Xnova\Controller;
use Xnova\Vars;

/**
 * @RoutePrefix("/xnsim")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 */
class XnsimController extends Controller
{
	private $usersInfo = [];

	public function initialize ()
	{
		$this->view->disableLevel(View::LEVEL_MAIN_LAYOUT);

		parent::initialize();

		$this->assets->clearJs();
		$this->assets->clearCss();
	}

	public function indexAction ()
	{
		$techList = [109, 110, 111, 120, 121, 122];

		$this->assets->addCss('assets/css/xnsim.css');
		$this->assets->addJs('assets/build/sim.js');
		$this->assets->addJs('assets/build/vendor.js');

		$this->view->setVar('techList', $techList);
	}

	public function reportAction ()
	{
		$this->assets->addCss('assets/build/app.css');

		if ($this->request->hasQuery('sid'))
		{
			$log = $this->db->query("SELECT * FROM game_log_sim WHERE sid = '".addslashes(htmlspecialchars($this->request->getQuery('sid', 'string', '')))."' LIMIT 1")->fetch();

			if (!isset($log['id']))
				die('Лога не существует');

			$result = json_decode($log['data'], true);

			$sid = $log['sid'];
		}
		else
		{
			$r = explode("|", $this->request->get('r', 'string', ''));

			if (!isset($r[0]) || !isset($r[10]))
				die('Нет данных для симуляции боя');

			define('MAX_SLOTS', $this->config->game->get('maxSlotsInSim', 5));

			include_once(ROOT_PATH."/app/config/battle.php");

			$attackers = $this->getAttackers(0, $r);
			$defenders = $this->getAttackers(MAX_SLOTS, $r);

			$engine = new Battle($attackers, $defenders);

			$report = $engine->getReport();

			$result = [];
			$result[0] = ['time' => time(), 'rw' => []];

			$result[1] = $this->convertPlayerGroupToArray($report->getResultAttackersFleetOnRound('START'));
			$result[2] = $this->convertPlayerGroupToArray($report->getResultDefendersFleetOnRound('START'));

			for ($_i = 0; $_i <= $report->getLastRoundNumber(); $_i++)
				$result[0]['rw'][] = $this->convertRoundToArray($report->getRound($_i));

			if ($report->attackerHasWin())
				$result[0]['won'] = 1;
			if ($report->defenderHasWin())
				$result[0]['won'] = 2;
			if ($report->isAdraw())
				$result[0]['won'] = 0;

			$result[0]['lost'] = ['att' => $report->getTotalAttackersLostUnits(), 'def' => $report->getTotalDefendersLostUnits()];

			$debris = $report->getDebris();

			$result[0]['debree']['att'] = $debris;
			$result[0]['debree']['def'] = [0, 0];

			$result[3] = ['metal' => 0, 'crystal' => 0, 'deuterium' => 0];
			$result[4] = $report->getMoonProb();
			$result[5] = '';

			$result[6] = [];

			foreach ($report->getDefendersRepaired() as $_id => $_player)
			{
				foreach ($_player as $_idFleet => $_fleet)
				{
					/** @var ShipType $_ship */
					foreach ($_fleet as $_shipID => $_ship)
						$result[6][$_idFleet][$_shipID] = $_ship->getCount();
				}
			}

			$statistics = [];

			for ($i = 0; $i < 50; $i++)
			{
				$engine = new Battle($attackers, $defenders);

				$report = $engine->getReport();

				$statistics[] = ['att' => $report->getTotalAttackersLostUnits(), 'def' => $report->getTotalDefendersLostUnits()];

				unset($report);
				unset($engine);
			}

			uasort($statistics, function($a, $b)
			{
				return ($a['att'] > $b['att'] ? 1 : -1);
			});

			$sid = md5(time().$this->request->getClientAddress());

			$check = $this->db->fetchColumn("SELECT COUNT(*) AS NUM FROM game_log_sim WHERE sid = '".$sid."'");

			if ($check == 0)
			{
				$this->db->insertAsDict('game_log_sim', [
					'sid' => $sid,
					'time' => time(),
					'data' => json_encode($result)
				]);
			}

			$this->view->setVar('statistics', $statistics);
		}

		$report = new CombatReport($result[0], $result[1], $result[2], $result[3], $result[4], $result[5], $result[6]);
		$report = $report->report();

		$this->view->setVar('report', $report);
		$this->view->setVar('sid', $sid);
	}

	private function convertPlayerGroupToArray (PlayerGroup $_playerGroup)
	{
		$result = [];

		foreach ($_playerGroup as $_player)
		{
			$result[$_player->getId()] = [
				'username' => $_player->getName(),
				'fleet' => [$_player->getId() => ['galaxy' => 1, 'system' => 1, 'planet' => 1]],
				'tech' => [
					'military_tech' => isset($this->usersInfo[$_player->getId()][109]) ? $this->usersInfo[$_player->getId()][109] : 0,
					'shield_tech' 	=> isset($this->usersInfo[$_player->getId()][110]) ? $this->usersInfo[$_player->getId()][110] : 0,
					'defence_tech' 	=> isset($this->usersInfo[$_player->getId()][111]) ? $this->usersInfo[$_player->getId()][111] : 0,
					'laser_tech'	=> isset($this->usersInfo[$_player->getId()][120]) ? $this->usersInfo[$_player->getId()][120] : 0,
					'ionic_tech'	=> isset($this->usersInfo[$_player->getId()][121]) ? $this->usersInfo[$_player->getId()][121] : 0,
					'buster_tech'	=> isset($this->usersInfo[$_player->getId()][122]) ? $this->usersInfo[$_player->getId()][122] : 0
				],
				'flvl' => $this->usersInfo[$_player->getId()],
			];
		}

		return $result;
	}

	private function convertRoundToArray(Round $round)
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

	private function getAttackers($s, $r)
	{
		$playerGroupObj = new PlayerGroup();

		$model = new \Xnova\Models\Fleet();

		for ($i = $s; $i < MAX_SLOTS * 2; $i++)
		{
			if ($i <= MAX_SLOTS && $i < (MAX_SLOTS + $s) && $r[$i] != "")
			{
				$res = [];
				$fleets = [];

				$rFleet = [];

				$fleetData = explode(';', $r[$i]);

				foreach ($fleetData as $data)
				{
					$f = explode(',', $data);

					if (isset($f[1]) && $f[1] > 0)
					{
						$rFleet[] = [
							'id' => $f[0],
							'count' => $f[1]
						];
					}
				}

				$fleetData = $model->getShips($rFleet);

				foreach ($fleetData as $shipId => $shipArr)
				{
					if ($shipId > 200)
						$fleets[$shipId] = [$shipArr['count'], 0];

					$res[$shipId] = $shipArr['count'];
				}

				$fleetId = $i;
				$playerId = $i;

				$playerObj = new Player($playerId);
				$playerObj->setName('Игрок ' . ($playerId + 1));
				$playerObj->setTech(0, 0, 0);

				$this->usersInfo[$playerId] = $res;

				$fleetObj = new Fleet($fleetId);

				foreach ($fleets as $id => $count)
				{
					$id = floor($id);

					if ($count[0] > 0 && $id > 0)
						$fleetObj->addShipType($this->getShipType($id, $count, $res));
				}

				if (!$fleetObj->isEmpty())
					$playerObj->addFleet($fleetObj);

				if (!$playerGroupObj->existPlayer($playerId))
					$playerGroupObj->addPlayer($playerObj);
			}
		}

		return $playerGroupObj;
	}

	private function getShipType($id, $count, $res)
	{
		$attDef 	= $count[1] + (isset($res[111]) ? $res[111] : 0) * 0.05;
		$attTech 	= (isset($res[109]) ? $res[109] : 0) * 0.05 + $count[1];

		if ($this->registry->CombatCaps[$id]['type_gun'] == 1)
			$attTech += (isset($res[120]) ? $res[120] : 0) * 0.05;
		elseif ($this->registry->CombatCaps[$id]['type_gun'] == 2)
			$attTech += (isset($res[121]) ? $res[121] : 0) * 0.05;
		elseif ($this->registry->CombatCaps[$id]['type_gun'] == 3)
			$attTech += (isset($res[122]) ? $res[122] : 0) * 0.05;

		$price = Vars::getItemPrice($id);

		$cost = [$price['metal'], $price['crystal']];

		if (Vars::getItemType($id) == Vars::ITEM_TYPE_FLEET)
			return new Ship($id, $count[0], $this->registry->CombatCaps[$id]['sd'], $this->registry->CombatCaps[$id]['shield'], $cost, $this->registry->CombatCaps[$id]['attack'], $attTech, ((isset($res[110]) ? $res[110] : 0) * 0.05), $attDef);

		return new Defense($id, $count[0], $this->registry->CombatCaps[$id]['sd'], $this->registry->CombatCaps[$id]['shield'], $cost, $this->registry->CombatCaps[$id]['attack'], $attTech, ((isset($res[110]) ? $res[110] : 0) * 0.05), $attDef);
	}
}