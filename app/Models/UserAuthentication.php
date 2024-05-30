<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAuthentication extends Model
{
	protected $guarded = [];
	protected $table = 'users_authentications';

	protected function casts(): array
	{
		return [
			'enter_time' => 'immutable_datetime',
		];
	}
}
