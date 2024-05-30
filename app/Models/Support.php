<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Support extends Model
{
	public $timestamps = false;

	protected function casts(): array
	{
		return [
			'time' => 'immutable_datetime',
		];
	}
}
