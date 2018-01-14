<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2018 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use Xnova\Controllers\Fleet\Back;
use Xnova\Controllers\Fleet\Quick;
use Xnova\Controllers\Fleet\Shortcut;
use Xnova\Controllers\Fleet\StageOne;
use Xnova\Controllers\Fleet\StageThree;
use Xnova\Controllers\Fleet\StageTwo;
use Xnova\Controllers\Fleet\StageZero;
use Xnova\Controllers\Fleet\Verband;
use Xnova\Fleet;
use Xnova\Controller;

/**
 * @RoutePrefix("/fleet")
 * @Route("/")
 * @Route("/{action}/")
 * @Route("/{action}{params:(/.*)*}")
 * @Private
 */
class FleetController extends Controller
{
	public function initialize ()
	{
		parent::initialize();
		
		if ($this->dispatcher->wasForwarded())
			return;
		
		$this->user->loadPlanet();

		// Устанавливаем обновлённые двигателя кораблей
		Fleet::SetShipsEngine($this->user);
	}

	/**
	 * @Route("/g{galaxy:[0-9]{1,2}}/s{system:[0-9]{1,3}}/p{planet:[0-9]{1,2}}/t{type:[0-9]{1}}/m{mission:[0-9]{1,2}}{params:(/.*)*}")
	 */
	public function indexAction ()
	{
		$action = new StageZero();
		$action->show($this);
	}

	public function stageoneAction ()
	{
		$action = new StageOne();
		$action->show($this);
	}

	public function stagetwoAction ()
	{
		$action = new StageTwo();
		$action->show($this);
	}

	public function stagethreeAction ()
	{
		$action = new StageThree();
		$action->show($this);
	}
	public function backAction ()
	{
		$action = new Back();
		$action->show($this);
	}

	public function shortcutAction ()
	{
		$action = new Shortcut();
		$action->show($this);
	}

	public function verbandAction ()
	{
		$action = new Verband();
		$action->show($this);
	}

	public function quickAction ()
	{
		$action = new Quick();

		$result = $action->show($this);

		$this->view->disable();

		if (!is_array($result))
		{
			$this->game->setRequestStatus(0);
			$this->game->setRequestMessage($result);
		}
		else
		{
			$this->game->setRequestStatus($result[0]);
			$this->game->setRequestMessage($result[1]);
		}
	}

	public function getShipInfo ($type)
	{
		$ship = [
			'id' 			=> $type,
			'consumption' 	=> Fleet::GetShipConsumption($type, $this->user),
			'speed' 		=> Fleet::GetFleetMaxSpeed("", $type, $this->user),
			'stay' 			=> $this->registry->CombatCaps[$type]['stay'],
		];

		if (isset($this->user->{'fleet_' . $type}) && isset($this->registry->CombatCaps[$type]['power_consumption']) && $this->registry->CombatCaps[$type]['power_consumption'] > 0)
			$ship['capacity'] = round($this->registry->CombatCaps[$type]['capacity'] * (1 + $this->user->{'fleet_' . $type} * ($this->registry->CombatCaps[$type]['power_consumption'] / 100)));
		else
			$ship['capacity'] = $this->registry->CombatCaps[$type]['capacity'];

		return $ship;
	}
}