<?php

namespace App\Models;

use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class LogStat extends Model
{
	use MassPrunable;

	protected $table = 'log_stats';
	protected $guarded = [];
	public $timestamps = false;

	protected $casts = [
		'time' => 'immutable_datetime',
	];

	public function prunable()
	{
		return static::query()->where('time', '<', now()->subDays(30));
	}
}
