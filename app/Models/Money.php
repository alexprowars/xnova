<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Money extends Model
{
	public $timestamps = false;
	protected $table = 'moneys';

	protected function casts(): array
	{
		return [
			'time' => 'datetime',
		];
	}
}
