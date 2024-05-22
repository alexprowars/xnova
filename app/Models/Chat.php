<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
	protected $table = 'chat';
	protected $guarded = [];

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
