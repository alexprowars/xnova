<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeed extends Seeder
{
	public function run()
	{
		app()[PermissionRegistrar::class]->forgetCachedPermissions();

		Role::create(['name' => 'admin']);
		Role::create(['name' => 'operator']);
		Role::create(['name' => 'super-operator']);
	}
}
