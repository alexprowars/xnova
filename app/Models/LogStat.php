<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogStat extends Model
{
	protected $guarded = false;
	public $timestamps = false;

	protected function casts(): array
	{
		return [
			'time' => 'immutable_datetime',
		];
	}
}
