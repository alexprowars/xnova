<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
	public $timestamps = false;

	protected function casts(): array
	{
		return [
			'time' => 'datetime',
		];
	}
}
