<?php

namespace App\Engine\CombatEngine\Utils;

abstract class GeometricDistribution
{
	public static function getProbabilityFromMean(float | int $m): float
	{
		if ($m <= 1) {
			return 1;
		}

		return 1 / $m;
	}

	public static function getMeanFromProbability(float | int $p): float
	{
		if ($p == 0) {
			return INF;
		}

		return 1 / $p;
	}

	public static function getVarianceFromProbability(float | int $p): float
	{
		if ($p == 0) {
			return INF;
		}

		return (1 - $p) / ($p * $p);
	}

	public static function getStandardDeviationFromProbability(float | int $p): float
	{
		return sqrt(self::getVarianceFromProbability($p));
	}
}
