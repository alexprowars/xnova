<?php

namespace App\Engine\Ai\Economy;

use App\Models\Planet;
use App\Services\FleetService;

class ResourceValue
{
	public const WEIGHTS = [
		'metal' => 1.0,
		'crystal' => 1.5,
		'deuterium' => 2.0,
	];

	public static function sum(array $resources): float
	{
		$value = 0.0;

		foreach (self::WEIGHTS as $resource => $weight) {
			$value += ($resources[$resource] ?? 0) * $weight;
		}

		return $value;
	}

	public static function loot(array $resources, int $capacity): float
	{
		$planet = new Planet(array_intersect_key($resources, self::WEIGHTS));

		return self::sum(FleetService::getSteal($planet, max(0, $capacity)));
	}
}
