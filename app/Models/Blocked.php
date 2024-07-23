<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blocked extends Model
{
	public $timestamps = false;
	public $table = 'users_blocked';
	protected $guarded = false;

	protected function casts(): array
	{
		return [
			'longer' => 'immutable_datetime',
		];
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	public function author()
	{
		return $this->belongsTo(User::class, 'author_id');
	}
}
