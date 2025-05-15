<?php

namespace App\Engine\Fleet;

class MissionFactory
{
	public static function getMissions(): array
	{
		return array_map(fn(Mission $mission) => self::getMission($mission), Mission::cases());
	}

	public static function getMission(Mission $mission): Missions\Mission
	{
		return match ($mission->value) {
			1 => resolve(Missions\Attack::class),
			2 => resolve(Missions\Assault::class),
			3 => resolve(Missions\Transport::class),
			4 => resolve(Missions\Stay::class),
			5 => resolve(Missions\StayAlly::class),
			6 => resolve(Missions\Spy::class),
			7 => resolve(Missions\Colonization::class),
			8 => resolve(Missions\Recycling::class),
			9 => resolve(Missions\Destruction::class),
			10 => resolve(Missions\CreateBase::class),
			15 => resolve(Missions\Expedition::class),
			20 => resolve(Missions\Rak::class),
		};
	}
}
