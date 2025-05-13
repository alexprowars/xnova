<?php

namespace App\Engine\CombatEngine\Utils;

abstract class Events
{
	public static function event_moon($moonProb)
	{
		$SizeMin = config('battle.MOON_MIN_START_SIZE') + ($moonProb * config('battle.MOON_MIN_FACTOR'));
		$SizeMax = config('battle.MOON_MAX_START_SIZE') + ($moonProb * config('battle.MOON_MAX_FACTOR'));
		$size = random_int($SizeMin, $SizeMax);
		$fields = floor(($size / 1000) ** 2);

		return ['size' => $size, 'fields' => $fields];
	}
}
