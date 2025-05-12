<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
	protected $guarded = false;

	protected $casts = [
		'users_id' => 'json:unicode',
		'data' => 'json:unicode',
	];
}
