<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogBattle extends Model
{
	protected $table = 'log_battles';
	protected $guarded = false;

	protected function casts(): array
	{
		return [
			'data' => 'array',
		];
	}
}
