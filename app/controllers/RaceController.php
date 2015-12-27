<?php

namespace App\Controllers;

use App\Sql;

class RaceController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();
	}
	
	public function show ()
	{
		global $reslist, $resource;
		
		$ui = $this->db->query('SELECT free_race_change FROM game_users_info WHERE id = ' . $this->user->id . ';', true);
		
		if (isset($_GET['sel']) && $this->user->race == 0)
		{
			$r = intval($_GET['sel']);
			$r = max(min($r, 4), 1);
		
			if ($r > 0)
			{
				Sql::build()->update('game_users')->set(Array('race' => intval($r), 'bonus' => (time() + 86400)));
		
				foreach ($reslist['officier'] AS $oId)
					Sql::build()->setField($resource[$oId], (time() + 86400));
					
				Sql::build()->where('id', '=', $this->user->id)->execute();
		
				$this->response->redirect("?set=tutorial");
			}
		}
		
		$isChangeAvailable = ($ui['free_race_change'] > 0) || ($this->user->credits >= 100);
		
		if (isset($_GET['mode']) && isset($_POST['race']) && $this->user->race != 0 && $isChangeAvailable)
		{
			$r = intval($_POST['race']);
			$r = max(min($r, 4), 1);
		
			if ($r > 0)
			{
				$queueManager = new queueManager();
				$queueCount = 0;

				$BuildOnPlanets = $this->db->query("SELECT `queue` FROM game_planets WHERE `id_owner` = '" . $this->user->id . "'");

				while ($BuildOnPlanet = $BuildOnPlanets->fetch())
				{
					$queueManager->loadQueue($BuildOnPlanet['queue']);

					$queueCount += $queueManager->getCount();
				}

				$UserFlyingFleets = $this->db->query("SELECT `fleet_id` FROM game_fleets WHERE `fleet_owner` = '" . $this->user->id . "'");

				if ($queueCount > 0)
					$this->message('Для смены фракции y вac нe дoлжнo идти cтpoитeльcтвo или иccлeдoвaниe нa плaнeтe.', "Oшибкa", "?set=race", 5);
				elseif ($UserFlyingFleets->numRows() > 0)
					$this->message('Для смены фракции y вac нe дoлжeн нaxoдитьcя флoт в пoлeтe.', "Oшибкa", "?set=race", 5);
				else
				{
					$this->db->query("UPDATE game_users SET race = " . $r . " WHERE id = " . $this->user->id . ";");
					
					if ($ui['free_race_change'] > 0)
						Sql::build()->update('game_users_info')->setField('-free_race_change', 1)->where('id', '=', $this->user->id)->execute();
					else
					{
						Sql::build()->update('game_users')->setField('-credits', 100)->where('id', '=', $this->user->id)->execute();
						Sql::build()->insert('game_log_credits')->set(Array('uid' => $this->user->id, 'time' => time(), 'credits' => 100, 'type' => 7))->execute();
					}	
						
					$this->db->query("UPDATE game_planets SET corvete = 0, interceptor = 0, dreadnought = 0, corsair = 0 WHERE id_owner = " . $this->user->id . ";");
		
					$this->response->redirect("?set=overview");
				}
			}
		}
		
		$this->view->pick('race');
		
		$this->view->setVar('race', $this->user->race, 'race');
		$this->view->setVar('free_race_change', $ui['free_race_change']);
		$this->view->setVar('isChangeAvailable', $isChangeAvailable);

		$this->tag->setTitle('Фракции');
		$this->showTopPanel(false);
		$this->showLeftPanel(!($this->user->race == 0));
	}
}

?>