<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
	public $timestamps = false;
	protected $guarded = [];

	public function user()
	{
		return $this->hasOne(User::class, 'id', 'user_id');
	}
}
