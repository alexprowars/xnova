<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserDetail;
use App\User;

class UserSeed extends Seeder
{
	public function run()
	{
		/** @var User $user */
		$user = User::query()->create([
			'username' => 'admin',
		]);

		UserDetail::query()->create([
			'email'    => 'admin@admin.com',
			'password' => bcrypt('password'),
		]);

		$user->assignRole('admin');
	}
}
