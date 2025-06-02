<?php

namespace App;

use Spatie\LaravelSettings;

class Settings extends LaravelSettings\Settings
{
	public int $lastSettedGalaxyPos;
	public int $lastSettedSystemPos;
	public int $lastSettedPlanetPos;
	public int $usersTotal;
	public int $usersOnline;
	public int $statUpdate;
	public int $activeUsers;
	public int $activeAlliance;
	public ?string $globalMessage;

	public static function group(): string
	{
		return 'general';
	}
}
