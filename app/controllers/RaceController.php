<?php

namespace App\Controllers;

use Xcms\db;
use Xcms\request;
use Xcms\sql;
use Xnova\User;
use Xnova\pageHelper;
use Xnova\queueManager;

class RaceController extends ApplicationController
{
	function __construct ()
	{
		parent::__construct();
	}
	
	public function show ()
	{
		global $reslist, $resource;
		
		$ui = db::query('SELECT free_race_change FROM game_users_info WHERE id = ' . user::get()->data['id'] . ';', true);
		
		if (isset($_GET['sel']) && user::get()->data['race'] == 0)
		{
			$r = intval($_GET['sel']);
			$r = max(min($r, 4), 1);
		
			if ($r > 0)
			{
				sql::build()->update('game_users')->set(Array('race' => intval($r), 'bonus' => (time() + 86400)));
		
				foreach ($reslist['officier'] AS $oId)
					sql::build()->setField($resource[$oId], (time() + 86400));
					
				sql::build()->where('id', '=', user::get()->data['id'])->execute();
		
				request::redirectTo("?set=tutorial");
			}
		}
		
		$isChangeAvailable = ($ui['free_race_change'] > 0) || (user::get()->data['credits'] >= 100);
		
		if (isset($_GET['mode']) && isset($_POST['race']) && user::get()->data['race'] != 0 && $isChangeAvailable)
		{
			$r = intval($_POST['race']);
			$r = max(min($r, 4), 1);
		
			if ($r > 0)
			{
				$queueManager = new queueManager();
				$queueCount = 0;

				$BuildOnPlanets = db::query("SELECT `queue` FROM game_planets WHERE `id_owner` = '" . user::get()->data['id'] . "'");

				while ($BuildOnPlanet = db::fetch($BuildOnPlanets))
				{
					$queueManager->loadQueue($BuildOnPlanet['queue']);

					$queueCount += $queueManager->getCount();
				}

				$UserFlyingFleets = db::query("SELECT `fleet_id` FROM game_fleets WHERE `fleet_owner` = '" . user::get()->data['id'] . "'");

				if ($queueCount > 0)
					$this->message('Для смены фракции y вac нe дoлжнo идти cтpoитeльcтвo или иccлeдoвaниe нa плaнeтe.', "Oшибкa", "?set=race", 5);
				elseif (db::num_rows($UserFlyingFleets) > 0)
					$this->message('Для смены фракции y вac нe дoлжeн нaxoдитьcя флoт в пoлeтe.', "Oшибкa", "?set=race", 5);
				else
				{
					db::query("UPDATE game_users SET race = " . $r . " WHERE id = " . user::get()->data['id'] . ";");
					
					if ($ui['free_race_change'] > 0)
						sql::build()->update('game_users_info')->setField('-free_race_change', 1)->where('id', '=', user::get()->data['id'])->execute();
					else
					{
						sql::build()->update('game_users')->setField('-credits', 100)->where('id', '=', user::get()->data['id'])->execute();
						sql::build()->insert('game_log_credits')->set(Array('uid' => user::get()->data['id'], 'time' => time(), 'credits' => 100, 'type' => 7))->execute();
					}	
						
					db::query("UPDATE game_planets SET corvete = 0, interceptor = 0, dreadnought = 0, corsair = 0 WHERE id_owner = " . user::get()->data['id'] . ";");
		
					request::redirectTo("?set=overview");
				}
			}
		}
		
		$this->setTemplate('race');
		
		$this->set('race', user::get()->data['race'], 'race');
		$this->set('free_race_change', $ui['free_race_change']);
		$this->set('isChangeAvailable', $isChangeAvailable);

		$this->setTitle('Фракции');
		$this->showTopPanel(false);
		$this->showLeftPanel(!(user::get()->data['race'] == 0));
		$this->display();
	}
}

?>