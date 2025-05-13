<?php

namespace App\Engine\CombatEngine\Utils;

use App\Engine\CombatEngine\Exception;

class Gauss
{
	public static function getNext(): int
	{
		$x = (float) mt_rand() / (float) mt_getrandmax();
		$y = (float) mt_rand() / (float) mt_getrandmax();

		return (int) sqrt(-2 * log($x)) * cos(2 * M_PI * $y);
	}

	public static function getNextMs(int $m, int $s): int
	{
		return self::getNext() * $s + $m;
	}

	public static function getNextMsBetween(int $m, int $s, int $min, int $max): int
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

		return 0;
	}
}
