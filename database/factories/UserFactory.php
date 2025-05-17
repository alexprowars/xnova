<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
	protected $model = User::class;

	public function definition(): array
	{
		return [
			'username' => $this->faker->title(),
			'email' => $this->faker->unique()->safeEmail(),
			'sex' => 1,
			'race' => 0,
			'email_verified_at' => now(),
			'password' => bcrypt('password'),
			'remember_token' => Str::random(10),
		];
	}
}
