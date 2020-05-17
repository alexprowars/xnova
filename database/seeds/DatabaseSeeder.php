<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
	public function run()
	{
		$this->call(PermissionSeed::class);
		$this->call(RoleSeed::class);
		$this->call(UserSeed::class);
	}
}
