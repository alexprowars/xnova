<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTech extends Model
{
	protected $table = 'users_teches';
	protected $guarded = false;

	protected $attributes = [
		'level' => 0,
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}
}
