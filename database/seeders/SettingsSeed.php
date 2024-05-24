<?php

namespace Database\Seeders;

use Backpack\Settings\app\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeed extends Seeder
{
	public function run()
	{
		Setting::create([
			'key' => 'lastSettedGalaxyPos',
			'name' => 'lastSettedGalaxyPos',
			'value' => 1,
			'field' => 'number',
			'active' => 1,
		]);

		Setting::create([
			'key' => 'lastSettedSystemPos',
			'name' => 'lastSettedSystemPos',
			'value' => 1,
			'field' => 'number',
			'active' => 1,
		]);

		Setting::create([
			'key' => 'lastSettedPlanetPos',
			'name' => 'lastSettedPlanetPos',
			'value' => 1,
			'field' => 'number',
			'active' => 1,
		]);

		Setting::create([
			'key' => 'usersTotal',
			'name' => 'usersTotal',
			'value' => 0,
			'field' => 'number',
			'active' => 1,
		]);

		Setting::create([
			'key' => 'usersOnline',
			'name' => 'usersOnline',
			'value' => 0,
			'field' => 'number',
			'active' => 1,
		]);

		Setting::create([
			'key' => 'statUpdate',
			'name' => 'statUpdate',
			'value' => 0,
			'field' => 'number',
			'active' => 1,
		]);

		Setting::create([
			'key' => 'activeUsers',
			'name' => 'activeUsers',
			'value' => 0,
			'field' => 'number',
			'active' => 1,
		]);

		Setting::create([
			'key' => 'activeAlliance',
			'name' => 'activeAlliance',
			'value' => 0,
			'field' => 'number',
			'active' => 1,
		]);
	}
}
