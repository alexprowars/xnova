<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Coordinates;
use App\Models\Planet;

class Assault extends BaseMission
{
	public function isMissionPossible(Planet $planet, Coordinates $target, ?Planet $targetPlanet, array $units = [], bool $isAssault = false): bool
	{
		return $isAssault && $targetPlanet;
	}

	public function targetEvent()
	{
		$this->return();
	}
}
