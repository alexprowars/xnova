<?php

namespace App\Engine\Fleet\CombatEngine\Utils;

use App\Engine\Fleet\CombatEngine\Exception;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

abstract class Math
{
	public static function divide(Number $num, Number $denum, $real = false)
	{
		if ($real) {
			if ($denum->result == 0) {
				throw new Exception('denum is zero');
			}

			$shots = floor($num->result / $denum->result);
			$rest = self::rest($num->result, $denum->result);

			return new Number($shots, $rest);
		} else {
			$shots = $num->result / $denum->result;

			return new Number($shots);
		}
	}

	public static function multiple(Number $first, Number $second, $real = false)
	{
		$result = $first->result * $second->result;

		if ($real) {
			return new Number(floor($result), $result - floor($result));
		}

		return new Number($result);
	}

	public static function heaviside($x, $y)
	{
		return ($x >= $y ? 1 : 0);
	}

	public static function rest($dividendo, $divisore, $real = true)
	{
		while ($divisore < 1) {
			$divisore *= 10;
			$dividendo *= 10;
		}
		if (!$real) {
			$decimal = (int)$dividendo - $dividendo;
			return $divisore % $dividendo + $decimal;
		}
		return $dividendo % $divisore;
	}

	public static function tryEvent($probability, $callback, $callbackParam)
	{
		if (!is_callable($callback)) {
			throw new Exception();
		}

		if (random_int(0, 99) < $probability) {
			return $callback($callbackParam);
		}

		return false;
	}

	public static function recursive_sum($array)
	{
		$sum = 0;
		$array_obj = new RecursiveIteratorIterator(new RecursiveArrayIterator($array));

		foreach ($array_obj as $value) {
			$sum += $value;
		}

		return $sum;
	}
}
