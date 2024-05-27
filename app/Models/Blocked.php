<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blocked extends Model
{
	public $timestamps = false;
	public $table = 'users_blocked';

	protected function casts(): array
	{
		return [
			'longer' => 'datetime',
		];
	}

	public function user()
	{
		return $this->hasOne(User::class, 'user_id');
	}

	public function author()
	{
		return $this->hasOne(User::class, 'author_id');
	}
}
