<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeed extends Seeder
{
	public function run()
	{
		app()[PermissionRegistrar::class]->forgetCachedPermissions();

		Permission::create(['name' => 'panel']);
		Permission::create(['name' => 'mailing']);
		Permission::create(['name' => 'server']);
		Permission::create(['name' => 'settings']);
		Permission::create(['name' => 'support']);
		Permission::create(['name' => 'users']);
		Permission::create(['name' => 'users-block']);
		Permission::create(['name' => 'users-unblock']);
		Permission::create(['name' => 'alliances']);
		Permission::create(['name' => 'content']);
		Permission::create(['name' => 'fleets']);
		Permission::create(['name' => 'messages']);
		Permission::create(['name' => 'moons']);
		Permission::create(['name' => 'payments']);
		Permission::create(['name' => 'planets']);
		Permission::create(['name' => 'roles']);

		app()[PermissionRegistrar::class]->forgetCachedPermissions();

		Role::create(['name' => 'admin']);
		Role::create(['name' => 'operator']);
		Role::create(['name' => 'super-operator']);
	}
}
