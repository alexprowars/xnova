<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeed extends Seeder
{
	public function run()
	{
		//Artisan::call('cache:clear');
		/** @noinspection PhpUndefinedMethodInspection */
		app()[PermissionRegistrar::class]->forgetCachedPermissions();

		//Permission::create(['name' => 'users_manage']);
	}
}
