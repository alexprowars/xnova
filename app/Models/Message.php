<?php

namespace App\Models;

use App\Engine\Enums\MessageType;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
	public $timestamps = false;

	protected function casts(): array
	{
		return [
			'time' => 'immutable_datetime',
			'type' => MessageType::class,
		];
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}
}
