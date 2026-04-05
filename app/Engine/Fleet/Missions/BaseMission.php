<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Coordinates;
use App\Engine\Fleet\FleetEngine;
use App\Models\Planet;

abstract class BaseMission extends FleetEngine implements Mission
{
	public static function isMissionPossible(Planet $planet, Coordinates $target, ?Planet $targetPlanet, array $units = [], bool $isAssault = false): bool
	{
		return true;
	}

	public function targetEvent(): void
	{
	}

	public function endStayEvent(): void
	{
	}

	public function returnEvent(): void
	{
		$this->restoreFleetToPlanet();
		$this->killFleet();
	}
}
