<?php

namespace App\Battle\Utils;

use Exception;

class Gauss
{
	/**
	 * Random::getNext()
	 * Return an random normal number
	 * @return int
	 */
	public static function getNext()
	{
		$x = (float)mt_rand() / (float)mt_getrandmax();
		$y = (float)mt_rand() / (float)mt_getrandmax();
		$u = sqrt(-2 * log($x)) * cos(2 * pi() * $y);

		return $u;
	}

	/**
	 * Random::getNextMs()
	 * Generates a random number from the normal distribution with specific mean and standard deviation
	 * @param int $m: mean
	 * @param int $s: standard deviation
	 * @return int
	 */
	public static function getNextMs($m, $s)
	{
		return self::getNext() * $s + $m;
	}

	/**
	 * Random::getNextMsBetween()
	 * Generates a random number from the normal distribution with specific mean and standard deviation.
	 * The number must be between min and max.
	 * @param int $m : mean
	 * @param int $s : standard deviation
	 * @param int $min : the minimum
	 * @param int $max : the maximum
	 * @return int
	 * @throws Exception
	 */
	public static function getNextMsBetween($m, $s, $min, $max)
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
				return mt_rand($min, $max);
			}
		}

		return 0;
	}
}

/**

 * //--------------------------- testing -----------------------

 * //--------edit only these!----------
 * define('MEAN', 55);
 * define('DEV', sqrt(7));
 * define('SIMULATIONS', 1000);
 * //----------------------------------


 * $a = [];
 * for ($i = 0; $i < SIMULATIONS; $i++)
 * {
 *	 $a[] = Gauss::getNextMs(MEAN, DEV);
 * }

 * $l = [];
 * foreach ($a as $v)
 * {
 *	 if (isset($l[$v]))
 *		 $l[$v]++;
 *	 else
 *		 $l[$v] = 1;
 * }
 * ksort($l);
 * foreach ($l as $id => $v)
 * {
 *	 $s = '';
 *	 for ($i = 0; $i < $v; $i++)
 *	 {
 *		 $s .= '-';
 *	 }
 *	 echo $s . $id . '(' . $v . ')' . '<br>';
 * }
 */
