<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hall extends Model
{
	public $timestamps = false;
	protected $table = 'halls';

	protected function casts(): array
	{
		return [
			'time' => 'datetime',
		];
	}
}
