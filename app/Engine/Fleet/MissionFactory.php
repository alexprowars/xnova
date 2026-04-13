<?php

namespace App\Engine\Fleet;

class MissionFactory
{
	public static function getMissions(): array
	{
		return array_map(fn(MissionType $mission) => self::getMission($mission), MissionType::cases());
	}

	/**
	 * @param MissionType $mission
	 * @return class-string<Missions\Mission>
	 */
	public static function getMission(MissionType $mission): string
	{
		return match ($mission->value) {
			1 => Missions\Attack::class,
			2 => Missions\Assault::class,
			3 => Missions\Transport::class,
			4 => Missions\Stay::class,
			5 => Missions\StayAlly::class,
			6 => Missions\Espionage::class,
			7 => Missions\Colonization::class,
			8 => Missions\Recycling::class,
			9 => Missions\Destruction::class,
			10 => Missions\CreateBase::class,
			15 => Missions\Expedition::class,
			20 => Missions\MissileAttack::class,
		};
	}
}
