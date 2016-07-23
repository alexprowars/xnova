<?php

namespace Xnova\Controllers;

/**
 * @author AlexPro
 * @copyright 2008 - 2016 XNova Game Group
 * Telegram: @alexprowars, Skype: alexprowars, Email: alexprowars@gmail.com
 */

use App\Controllers\Fleet\Back;
use App\Controllers\Fleet\Quick;
use App\Controllers\Fleet\Shortcut;
use App\Controllers\Fleet\StageOne;
use App\Controllers\Fleet\StageThree;
use App\Controllers\Fleet\StageTwo;
use App\Controllers\Fleet\StageZero;
use App\Controllers\Fleet\Verband;
use App\Fleet;
use Xnova\Controller;

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
			'stay' 			=> $this->storage->CombatCaps[$type]['stay'],
		];

		if (isset($this->user->{'fleet_' . $type}) && isset($this->storage->CombatCaps[$type]['power_consumption']) && $this->storage->CombatCaps[$type]['power_consumption'] > 0)
			$ship['capacity'] = round($this->storage->CombatCaps[$type]['capacity'] * (1 + $this->user->{'fleet_' . $type} * ($this->storage->CombatCaps[$type]['power_consumption'] / 100)));
		else
			$ship['capacity'] = $this->storage->CombatCaps[$type]['capacity'];

		return $ship;
	}
}