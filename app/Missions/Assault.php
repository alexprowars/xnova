<?php

namespace App\Missions;

use App\FleetEngine;

class Assault extends FleetEngine implements Mission
{
	public function targetEvent()
	{
		$this->fleet->return();
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