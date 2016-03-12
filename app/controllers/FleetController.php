<?php
namespace App\Controllers;

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

class FleetController extends ApplicationController
{
	public function initialize ()
	{
		parent::initialize();
		
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
		$action->show($this);
	}
}