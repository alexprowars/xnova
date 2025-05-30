<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeed extends Seeder
{
	public function run()
	{
		//Artisan::call('cache:clear');
		app()[PermissionRegistrar::class]->forgetCachedPermissions();

		//Permission::create(['name' => 'users_manage']);
	}
}
