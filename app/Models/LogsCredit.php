<?php

namespace App\Models;

use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class LogsCredit extends Model
{
	use MassPrunable;

	protected $table = 'logs_credits';
	protected $guarded = [];
	public $timestamps = false;

	protected $casts = [
		'created_at' => 'immutable_datetime',
	];

	public function prunable()
	{
		return static::query()->where('created_at', '<', now()->subDays(365));
	}
}
