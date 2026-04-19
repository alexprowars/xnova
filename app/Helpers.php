<?php

namespace App;

use RecursiveArrayIterator;
use RecursiveIteratorIterator;

class Helpers
{
	public static function convertIp(string|int $ip): string
	{
		if (!is_numeric($ip)) {
			return sprintf("%u", ip2long($ip));
		}

		return long2ip($ip);
	}

	public static function recursiveSum(array $array): int|float
	{
		$sum = 0;
		$array_obj = new RecursiveIteratorIterator(new RecursiveArrayIterator($array));

		foreach ($array_obj as $value) {
			$sum += $value;
		}

		return $sum;
	}
}
