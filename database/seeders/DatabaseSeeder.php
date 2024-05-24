<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
	public function run()
	{
		$this->call(SettingsSeed::class);
		$this->call(PermissionSeed::class);
		$this->call(RoleSeed::class);
		$this->call(UserSeed::class);
	}
}
