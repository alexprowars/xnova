<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogAttack extends Model
{
	protected $guarded = false;

	protected function casts(): array
	{
		return [
			'fleet' => 'array',
		];
	}
}
