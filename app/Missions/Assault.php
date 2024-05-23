<?php

namespace App\Missions;

use App\FleetEngine;

class Assault extends FleetEngine implements Mission
{
	public function targetEvent()
	{
		$this->returnFleet();
	}

	public function endStayEvent()
	{
		return;
	}

	public function returnEvent()
	{
		$this->restoreFleetToPlanet();
		$this->killFleet();
	}
}