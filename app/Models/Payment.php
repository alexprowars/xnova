<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
	public $timestamps = false;
	protected $guarded = false;

	public function user()
	{
		return $this->belongsTo(User::class, 'id', 'user_id');
	}
}
