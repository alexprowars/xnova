<?php

namespace App\Engine\Fleet\CombatEngine\Utils;

use App\Engine\Fleet\CombatEngine\Exception;

class Gauss
{
	public static function getNext(): float
	{
		$x = (float) mt_rand() / (float) mt_getrandmax();
		$y = (float) mt_rand() / (float) mt_getrandmax();

		return (int) sqrt(-2 * log($x)) * cos(2 * M_PI * $y);
	}

	public static function getNextMs(int $m, int $s): float
	{
		return self::getNext() * $s + $m;
	}

	public static function getNextMsBetween(int | float $m, int | float $s, int | float $min, int | float $max): float
	{
		$i = 0;

		if ($min > $m || $max < $m) {
			throw new Exception("Mean is not bounded by min and max");
		}

		while (true) {
			$n = self::getNextMs($m, $s);

			if ($n >= $min && $n <= $max) {
				return $n;
			}

			$i++;

			if ($i > 10) {
				return random_int($min, $max);
			}
		}
	}
}
