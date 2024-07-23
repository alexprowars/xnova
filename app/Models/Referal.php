<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referal extends Model
{
	public $timestamps = false;
	protected $guarded = false;

	public function user()
	{
		return $this->belongsTo(User::class, 'u_id');
	}

	public function referal()
	{
		return $this->belongsTo(User::class, 'r_id');
	}
}
