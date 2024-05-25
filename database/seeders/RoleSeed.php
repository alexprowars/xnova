<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeed extends Seeder
{
	public function run()
	{
		$role = Role::findOrCreate('admin');
		//$role->givePermissionTo('users_manage');
	}
}
