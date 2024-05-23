<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTech extends Model
{
	public $table = 'users_teches';
	public $timestamps = false;

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}
}
