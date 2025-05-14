<?php

namespace App\Models;

use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
	use MassPrunable;

	protected $guarded = false;

	protected $casts = [
		'users_id' => 'json:unicode',
		'data' => 'json:unicode',
	];

	public function prunable()
	{
		return static::query()->where('created_at', '<', now()->subDays(7));
	}
}
