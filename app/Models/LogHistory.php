<?php

namespace App\Models;

use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class LogHistory extends Model
{
	use MassPrunable;

	protected $table = 'log_histories';
	protected $guarded = false;

	public function prunable()
	{
		return static::query()->where('created_at', '<', now()->subDays(7));
	}
}
