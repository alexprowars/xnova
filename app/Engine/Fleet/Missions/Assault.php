<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\FleetEngine;

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