<?php

namespace Database\Seeders;

use Backpack\Settings\app\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeed extends Seeder
{
	public function run()
	{
		Setting::firstOrCreate(['key' => 'lastSettedGalaxyPos'], [
			'key' => 'lastSettedGalaxyPos',
			'name' => 'lastSettedGalaxyPos',
			'value' => 1,
			'field' => 'number',
			'active' => 1,
		]);

		Setting::firstOrCreate(['key' => 'lastSettedSystemPos'], [
			'key' => 'lastSettedSystemPos',
			'name' => 'lastSettedSystemPos',
			'value' => 1,
			'field' => 'number',
			'active' => 1,
		]);

		Setting::firstOrCreate(['key' => 'lastSettedPlanetPos'], [
			'key' => 'lastSettedPlanetPos',
			'name' => 'lastSettedPlanetPos',
			'value' => 1,
			'field' => 'number',
			'active' => 1,
		]);

		Setting::firstOrCreate(['key' => 'usersTotal'], [
			'key' => 'usersTotal',
			'name' => 'usersTotal',
			'value' => 0,
			'field' => 'number',
			'active' => 1,
		]);

		Setting::firstOrCreate(['key' => 'usersOnline'], [
			'key' => 'usersOnline',
			'name' => 'usersOnline',
			'value' => 0,
			'field' => 'number',
			'active' => 1,
		]);

		Setting::firstOrCreate(['key' => 'statUpdate'], [
			'key' => 'statUpdate',
			'name' => 'statUpdate',
			'value' => 0,
			'field' => 'number',
			'active' => 1,
		]);

		Setting::firstOrCreate(['key' => 'activeUsers'], [
			'key' => 'activeUsers',
			'name' => 'activeUsers',
			'value' => 0,
			'field' => 'number',
			'active' => 1,
		]);

		Setting::firstOrCreate(['key' => 'activeAlliance'], [
			'key' => 'activeAlliance',
			'name' => 'activeAlliance',
			'value' => 0,
			'field' => 'number',
			'active' => 1,
		]);
	}
}
