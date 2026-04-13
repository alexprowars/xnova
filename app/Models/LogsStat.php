<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

	/**
	 * @return Builder<static>
	 */
	public function prunable(): Builder
	{
		return static::query()->where('date', '<', now()->subDays(30));
	}
}
