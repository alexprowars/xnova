<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Fleet\FleetEngine;

class BaseMission extends FleetEngine implements Mission
{
	public function targetEvent()
	{
	}

	public function endStayEvent()
	{
	}

	public function returnEvent()
	{
		$this->restoreFleetToPlanet();
		$this->killFleet();
	}
}
