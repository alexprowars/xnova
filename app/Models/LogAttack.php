<?php

namespace App\Models;

use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class LogAttack extends Model
{
	use MassPrunable;

	protected $table = 'log_attacks';
	protected $guarded = false;

	protected $casts = [
		'fleet' => 'json:unicode',
	];

	public function prunable()
	{
		return static::query()->where('created_at', '<', now()->subDays(7));
	}
}
