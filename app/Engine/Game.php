<?php

namespace App\Engine;

use Carbon\Carbon;

class Game
{
	public static function datezone(string $format, int|string|Carbon|null $time = null): string
	{
		$date = null;

		if ($time instanceof Carbon) {
			$date = $time;
		}

		if (is_numeric($time)) {
			$date = Carbon::createFromTimestamp($time);
		}

		if (is_string($time)) {
			$date = Carbon::parse($time);
		}

		if (empty($time)) {
			$date = now();
		}

		$timezone = auth()->user()?->getOption('timezone');

		if (!empty($timezone)) {
			$date = $date->setTimezone($timezone);
		}

		return $date->format($format);
	}

	public static function getSpeed(?string $type = null): float
	{
		if ($type == 'fleet') {
			return (float) config('game.fleet_speed', 1);
		}
		if ($type == 'mine') {
			return (float) config('game.resource_multiplier', 1);
		}
		if ($type == 'build') {
			return (float) config('game.game_speed', 1);
		}

		return 1.0;
	}

	public static function getMerchantExchangeRate(): array
	{
		return [
			'metal' => 1,
			'crystal' => 2,
			'deuterium' => 4,
		];
	}
}
