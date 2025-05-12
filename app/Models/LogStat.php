<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogStat extends Model
{
	protected $guarded = false;
	public $timestamps = false;

	protected $casts = [
		'time' => 'immutable_datetime',
	];
}
