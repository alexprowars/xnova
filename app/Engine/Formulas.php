<?php

namespace App\Engine;

class Formulas
{
	public static function getMissileRange(int $impulseDrive): int
	{
		if ($impulseDrive > 0) {
			return ($impulseDrive * 5) - 1;
		}

		return 0;
	}

	public static function getPhalanxRange(int $phalanxLevel): int
	{
		if ($phalanxLevel > 1) {
			return ($phalanxLevel ** 2) - 1;
		} else {
			return 0;
		}
	}

	public static function getMoonDestructionChance(int $planetDiameter, int $deathStars): int
	{
		$chance = (100 - sqrt($planetDiameter)) * sqrt($deathStars);

		return min(100, (int) round($chance));
	}

	public static function getDeathStarsDestructionChance(int $planetDiameter, int $deathStars): int
	{
		$chance = sqrt($planetDiameter) / 4;

		if ($deathStars > 150) {
			$chance *= 0.1;
		} elseif ($deathStars > 100) {
			$chance *= 0.25;
		} elseif ($deathStars > 50) {
			$chance *= 0.5;
		} elseif ($deathStars > 25) {
			$chance *= 0.75;
		}

		return (int) $chance;
	}
}
