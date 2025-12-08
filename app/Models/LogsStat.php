<?php

namespace App\Models;

use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class LogsStat extends Model
{
	use MassPrunable;

	protected $table = 'logs_stats';
	protected $guarded = [];
	public $timestamps = false;

	protected $casts = [
		'date' => 'immutable_datetime',
	];

	public function prunable()
	{
		return static::query()->where('date', '<', now()->subDays(30));
	}
}
