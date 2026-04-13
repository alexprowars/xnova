<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class LogsAttack extends Model
{
	use MassPrunable;

	protected $table = 'logs_attacks';
	protected $guarded = [];
	public $timestamps = false;

	protected $casts = [
		'fleet' => 'json:unicode',
		'created_at' => 'immutable_datetime',
	];

	/**
	 * @return Builder<static>
	 */
	public function prunable(): Builder
	{
		return static::query()->where('created_at', '<', now()->subDays(7));
	}
}
