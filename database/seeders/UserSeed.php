<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeed extends Seeder
{
	public function run()
	{
		if (!User::find(1)) {
			$user = User::creation([
				'username' => 'admin',
				'email'    => 'admin@admin.com',
				'password' => 'password',
			]);

			$user->assignRole('admin');
		}
	}
}
