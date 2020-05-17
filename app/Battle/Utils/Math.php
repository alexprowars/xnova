<?php

namespace Xnova\Battle\Utils;

use Exception;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Xnova\Battle\Utils\Number as Number2;

abstract class Math
{
	public static function divide(Number2 $num, Number2 $denum, $real = false)
	{
		if ($real) {
			if ($denum->result == 0) {
				throw new Exception('denum is zero');
			}

			$shots = floor($num->result / $denum->result);
			$rest = Math::rest($num->result, $denum->result);

			return new Number($shots, $rest);
		} else {
			$shots = $num->result / $denum->result;

			return new Number($shots);
		}
	}

	public static function multiple(Number2 $first, Number2 $second, $real = false)
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

		if (mt_rand(0, 99) < $probability) {
			return call_user_func($callback, $callbackParam);
		}

		return false;
	}

	public static function recursive_sum($array)
	{
		$sum = 0;
		$array_obj = new RecursiveIteratorIterator(new RecursiveArrayIterator($array));

		foreach ($array_obj as $key => $value) {
			$sum += $value;
		}

		return $sum;
	}
}
