<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Exceptions\RedirectException;
use Xnova\Models\Fleet;
use Xnova\Models\Planet;
use Xnova\Queue;
use Xnova\Controller;
use Xnova\Request;
use Xnova\Vars;

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
				$queueManager = new Queue($this->user);
				$queueCount = $queueManager->getCount();

				$UserFlyingFleets = Fleet::count(['owner = ?0', 'bind' => [$this->user->id]]);

				if ($queueCount > 0)
					throw new RedirectException('Для смены фракции y вac нe дoлжнo идти cтpoитeльcтвo или иccлeдoвaниe нa плaнeтe.', "/race/");
				elseif ($UserFlyingFleets > 0)
					throw new RedirectException('Для смены фракции y вac нe дoлжeн нaxoдитьcя флoт в пoлeтe.', "/race/");
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

					$planets = Planet::find([
						'conditions' => 'id_owner = :user:',
						'bind' => [
							'user' => $this->user->id
						]
					]);

					foreach ($planets as $planet)
					{
						$planet->setUnit(Vars::getIdByName('corvete'), 0);
						$planet->setUnit(Vars::getIdByName('interceptor'), 0);
						$planet->setUnit(Vars::getIdByName('dreadnought'), 0);
						$planet->setUnit(Vars::getIdByName('corsair'), 0);

						$planet->update();
					}

					throw new RedirectException('Фракция изменена', '/overview/');
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

				foreach (Vars::getItemsByType(Vars::ITEM_TYPE_OFFICIER) AS $oId)
					$update[Vars::getName($oId)] = time() + 86400;
					
				$this->user->update($update);

				throw new RedirectException('', 'tutorial/');
			}
		}

		$isChangeAvailable = ($numChanges > 0) || ($this->user->credits >= 100);

		Request::addData('page' , [
			'change' => (int) $numChanges,
			'change_available' => $isChangeAvailable
		]);
		
		$this->tag->setTitle('Фракции');
		$this->showLeftPanel(!($this->user->race == 0));
	}
}