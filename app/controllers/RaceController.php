<?php
namespace App\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use App\Models\Fleet;
use App\Queue;

class RaceController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();
	}

	public function changeAction ()
	{
		$numChanges = $this->db->fetchColumn('SELECT free_race_change FROM game_users_info WHERE id = ' . $this->user->id);

		$isChangeAvailable = ($numChanges > 0) || ($this->user->credits >= 100);

		if ($this->user->race != 0 && $isChangeAvailable)
		{
			$r = $this->request->getPost('race', 'int', 0);
			$r = max(min($r, 4), 1);

			if ($r > 0)
			{
				$queueManager = new Queue();
				$queueCount = 0;

				$BuildOnPlanets = $this->db->query("SELECT `queue` FROM game_planets WHERE `id_owner` = '" . $this->user->id . "'");

				while ($BuildOnPlanet = $BuildOnPlanets->fetch())
				{
					$queueManager->loadQueue($BuildOnPlanet['queue']);

					$queueCount += $queueManager->getCount();
				}

				$UserFlyingFleets = Fleet::count(['owner = ?0', 'bind' => [$this->user->id]]);

				if ($queueCount > 0)
					$this->message('Для смены фракции y вac нe дoлжнo идти cтpoитeльcтвo или иccлeдoвaниe нa плaнeтe.', "Oшибкa", "/race/", 5);
				elseif ($UserFlyingFleets > 0)
					$this->message('Для смены фракции y вac нe дoлжeн нaxoдитьcя флoт в пoлeтe.', "Oшибкa", "/race/", 5);
				else
				{
					$this->db->query("UPDATE game_users SET race = " . $r . " WHERE id = " . $this->user->id . ";");

					if ($numChanges > 0)
						$this->user->saveData(['-free_race_change' => 1]);
					else
					{
						$this->user->saveData(['-credits' => 100]);
						$this->db->insertAsDict('game_log_credits', ['uid' => $this->user->id, 'time' => time(), 'credits' => 100, 'type' => 7]);
					}

					$this->db->query("UPDATE game_planets SET corvete = 0, interceptor = 0, dreadnought = 0, corsair = 0 WHERE id_owner = " . $this->user->id . ";");

					$this->response->redirect("overview/");
				}
			}
		}
	}
	
	public function indexAction ()
	{
		$numChanges = $this->db->fetchColumn('SELECT free_race_change FROM game_users_info WHERE id = ' . $this->user->id);
		
		if (isset($_GET['sel']) && $this->user->race == 0)
		{
			$r = intval($_GET['sel']);
			$r = max(min($r, 4), 1);
		
			if ($r > 0)
			{
				$update = ['race' => intval($r), 'bonus' => time() + 86400];

				foreach ($this->storage->reslist['officier'] AS $oId)
					$update[$this->storage->resource[$oId]] = time() + 86400;
					
				$this->user->saveData($update);
		
				$this->response->redirect("/tutorial/");
			}
		}

		$isChangeAvailable = ($numChanges > 0) || ($this->user->credits >= 100);
		
		$this->view->setVar('race', $this->user->race);
		$this->view->setVar('free_race_change', $numChanges);
		$this->view->setVar('isChangeAvailable', $isChangeAvailable);

		$this->tag->setTitle('Фракции');
		$this->showTopPanel(false);
		$this->showLeftPanel(!($this->user->race == 0));
	}
}