<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
	public function up(): void
	{
		$this->migrator->add('general.lastSettedGalaxyPos', 1);
		$this->migrator->add('general.lastSettedSystemPos', 1);
		$this->migrator->add('general.lastSettedPlanetPos', 1);
		$this->migrator->add('general.usersTotal', 0);
		$this->migrator->add('general.usersOnline', 0);
		$this->migrator->add('general.statUpdate', 0);
		$this->migrator->add('general.activeUsers', 0);
		$this->migrator->add('general.activeAlliance', 0);
	}
};
