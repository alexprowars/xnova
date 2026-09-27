<?php

namespace App\Engine\Ai\Development;

class UnitTargets
{
	// Уровень шахты металла открывает этап; требования берутся из игрового каталога.
	private const array SUPPORT = [
		202 => ['mine' => 6, 'divisor' => 5, 'minimum' => 2, 'score' => 85],
		203 => ['mine' => 10, 'divisor' => 15, 'minimum' => 2, 'score' => 70],
		209 => ['mine' => 10, 'divisor' => 20, 'minimum' => 1, 'score' => 55],
		216 => ['mine' => 24, 'divisor' => 1000, 'minimum' => 1, 'score' => 45],
	];

	private const array COMBAT = [
		204 => ['mine' => 6, 'divisor' => 1, 'minimum' => 1, 'score' => 60],
		205 => ['mine' => 10, 'divisor' => 5, 'minimum' => 2, 'score' => 65],
		206 => ['mine' => 14, 'divisor' => 8, 'minimum' => 3, 'score' => 80],
		207 => ['mine' => 18, 'divisor' => 15, 'minimum' => 3, 'score' => 85],
		211 => ['mine' => 22, 'divisor' => 40, 'minimum' => 2, 'score' => 75],
		213 => ['mine' => 24, 'divisor' => 50, 'minimum' => 2, 'score' => 75],
		214 => ['mine' => 30, 'divisor' => 1500, 'minimum' => 1, 'score' => 60],
		215 => ['mine' => 22, 'divisor' => 25, 'minimum' => 3, 'score' => 80],
	];

	private const array DEFENSE = [
		401 => ['mine' => 6, 'divisor' => 3, 'minimum' => 5, 'score' => 45],
		402 => ['mine' => 10, 'divisor' => 4, 'minimum' => 1, 'score' => 40],
		403 => ['mine' => 14, 'divisor' => 12, 'minimum' => 2, 'score' => 45],
		404 => ['mine' => 18, 'divisor' => 30, 'minimum' => 2, 'score' => 45],
		405 => ['mine' => 16, 'divisor' => 16, 'minimum' => 2, 'score' => 45],
		406 => ['mine' => 24, 'divisor' => 60, 'minimum' => 1, 'score' => 50],
	];

	/** @return array<int, array{count: int, score: int, reason: string}> */
	public static function forDevelopment(int $metalLevel, StrategyType $strategy): array
	{
		$scale = max(1, ($metalLevel - 4) ** 2);
		$military = match ($strategy) {
			StrategyType::MILITARY => 1.5,
			StrategyType::ECONOMY => 0.5,
			StrategyType::BALANCED => 1.0,
		};
		$targets = [];

		foreach ([
			[self::SUPPORT, 1.0, 'Logistics and support fleet'],
			[self::COMBAT, $military, 'Diversify the strike fleet'],
			[self::DEFENSE, 1.0, 'Layered planetary defense'],
		] as [$profiles, $factor, $reason]) {
			foreach ($profiles as $id => $profile) {
				if ($metalLevel < $profile['mine']) {
					continue;
				}

				$targets[$id] = [
					'count' => max($profile['minimum'], (int) ceil($scale * $factor / $profile['divisor'])),
					'score' => $profile['score'],
					'reason' => $reason,
				];
			}
		}

		return $targets;
	}
}
