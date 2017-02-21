<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Exceptions\RedirectException;
use Xnova\Models\Fleet;
use Xnova\Models\Planet;
use Xnova\Queue;
use Xnova\Controller;

/**
 * @RoutePrefix("/race")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class RaceController extends Controller
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

				$BuildOnPlanets = Planet::find(['columns' => 'queue', 'conditions' => 'id_owner = ?0', 'bind' => [$this->user->id]]);

				foreach ($BuildOnPlanets as $BuildOnPlanet)
				{
					$queueManager->loadQueue($BuildOnPlanet->queue);

					$queueCount += $queueManager->getCount();
				}

				$UserFlyingFleets = Fleet::count(['owner = ?0', 'bind' => [$this->user->id]]);

				if ($queueCount > 0)
					throw new RedirectException('Для смены фракции y вac нe дoлжнo идти cтpoитeльcтвo или иccлeдoвaниe нa плaнeтe.', "Oшибкa", "/race/", 5);
				elseif ($UserFlyingFleets > 0)
					throw new RedirectException('Для смены фракции y вac нe дoлжeн нaxoдитьcя флoт в пoлeтe.', "Oшибкa", "/race/", 5);
				else
				{
					$this->user->race = $r;

					if ($numChanges > 0)
						$this->db->updateAsDict('game_users_info', ['-free_race_change' => 1], 'id = '.$this->user->id);
					else
					{
						$this->user->credits -= 100;
						$this->db->insertAsDict('game_log_credits', ['uid' => $this->user->id, 'time' => time(), 'credits' => 100, 'type' => 7]);
					}

					$this->user->update();

					$this->db->query("UPDATE game_planets SET corvete = 0, interceptor = 0, dreadnought = 0, corsair = 0 WHERE id_owner = " . $this->user->id . ";");

					$this->response->redirect("overview/");
				}
			}
		}
	}
	
	public function indexAction ()
	{
		$numChanges = $this->db->fetchColumn('SELECT free_race_change FROM game_users_info WHERE id = ' . $this->user->id);
		
		if ($this->request->hasQuery('sel') && $this->user->race == 0)
		{
			$r = $this->request->getQuery('sel', 'int', 0);
			$r = max(min($r, 4), 1);
		
			if ($r > 0)
			{
				$update = ['race' => intval($r), 'bonus' => time() + 86400];

				foreach ($this->registry->reslist['officier'] AS $oId)
					$update[$this->registry->resource[$oId]] = time() + 86400;
					
				$this->user->update($update);
		
				$this->response->redirect("tutorial/");
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