<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\User;
use Illuminate\Support\Facades\Hash;

class UserSeed extends Seeder
{
	public function run()
	{
		$user = User::create([
			'username' => 'admin',
			'email'    => 'admin@admin.com',
			'password' => Hash::make('password'),
		]);

		$user->assignRole('admin');
	}
}
