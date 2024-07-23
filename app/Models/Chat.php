<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
	protected $table = 'chat';
	protected $guarded = false;

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
