<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
	protected function casts(): array
	{
		return [
			'users_id' => 'array',
			'data' => 'array',
		];
	}
}
