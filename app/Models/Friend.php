<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
	protected $guarded = [];

	public function user()
	{
		return $this->hasOne(User::class);
	}

	public function friend()
	{
		return $this->hasOne(User::class);
	}
}
