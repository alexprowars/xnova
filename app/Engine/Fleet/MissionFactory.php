<?php

namespace App\Engine\Fleet;

class MissionFactory
{
	public static function getMissions(): array
	{
		return array_map(fn(Mission $mission) => self::getMission($mission), Mission::cases());
	}

	/**
	 * @param Mission $mission
	 * @return class-string<Missions\Mission>
	 */
	public static function getMission(Mission $mission): string
	{
		return match ($mission->value) {
			1 => Missions\Attack::class,
			2 => Missions\Assault::class,
			3 => Missions\Transport::class,
			4 => Missions\Stay::class,
			5 => Missions\StayAlly::class,
			6 => Missions\Spy::class,
			7 => Missions\Colonization::class,
			8 => Missions\Recycling::class,
			9 => Missions\Destruction::class,
			10 => Missions\CreateBase::class,
			15 => Missions\Expedition::class,
			20 => Missions\Rak::class,
		};
	}
}
