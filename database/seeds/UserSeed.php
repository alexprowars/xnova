<?php

use Illuminate\Database\Seeder;
use Xnova\User;

class UserSeed extends Seeder
{
	public function run()
	{
		/** @var User $user */
		$user = User::query()->create([
			'name'     => 'Admin',
			'email'    => 'admin@admin.com',
			'password' => bcrypt('password'),
		]);

		$user->assignRole('admin');
	}
}
