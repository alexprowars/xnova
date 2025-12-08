<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class LogsSimulation extends Model
{
	use HasUuids;

	protected $table = 'logs_simulations';
	protected $guarded = [];
	public $timestamps = false;

	protected $casts = [
		'data' => 'json:unicode',
		'created_at' => 'immutable_datetime',
	];

	public function prunable()
	{
		return static::query()->where('created_at', '<', now()->subDays(365));
	}
}
