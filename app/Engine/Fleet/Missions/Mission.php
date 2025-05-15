<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Coordinates;
use App\Models\Planet;

interface Mission
{
	public function isMissionPossible(Planet $planet, Coordinates $target, ?Planet $targetPlanet, array $units, bool $isAssault = false): bool;

	public function targetEvent();

	public function endStayEvent();

	public function returnEvent();
}
