<?php

namespace App\Controllers;

use App\Controllers\Fleet\Back;
use App\Controllers\Fleet\StageOne;
use App\Controllers\Fleet\StageZero;
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
		new StageZero($this);
	}

	public function stageoneAction ()
	{
		new StageOne($this);
	}

	public function stagetwoAction ()
	{
		new StageTwo($this);
	}

	public function stagethreeAction ()
	{
		new StageThree($this);
	}
	public function backAction ()
	{
		new Back($this);
	}

	public function shortcutAction ()
	{
		new Shortcut($this);
	}

	public function verbandAction ()
	{
		new Verband($this);
	}

	public function quickAction ()
	{
		new Quick($this);
	}
}

?>