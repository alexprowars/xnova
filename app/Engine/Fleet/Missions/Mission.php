<?php

namespace App\Engine\Fleet\Missions;

use App\Engine\Coordinates;
use App\Models\Planet;

interface Mission
{
	public static function isMissionPossible(Planet $planet, Coordinates $target, ?Planet $targetPlanet, array $units, bool $isAssault = false): bool;

	public function targetEvent(): void;

	public function endStayEvent(): void;

	public function returnEvent(): void;
}
