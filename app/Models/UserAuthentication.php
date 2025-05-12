<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAuthentication extends Model
{
	protected $guarded = false;
	protected $table = 'users_authentications';

	protected $casts = [
		'enter_time' => 'immutable_datetime',
	];
}
