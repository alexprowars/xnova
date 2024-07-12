<?php

namespace App\Models;

use App\Engine\Enums\MessageType;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
	public $timestamps = false;

	protected $fillable = [
		'user_id',
		'from_id',
		'theme',
		'time',
		'text',
	];

	protected function casts(): array
	{
		return [
			'time' => 'immutable_datetime',
			'type' => MessageType::class,
		];
	}

	public function from()
	{
		return $this->belongsTo(User::class, 'from_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}
}
