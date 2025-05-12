<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Blocked extends Model
{
	public $table = 'users_blocked';
	protected $guarded = false;

	protected $casts = [
		'longer' => 'immutable_datetime',
	];

	/** @return BelongsTo<User, $this> */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	/** @return BelongsTo<User, $this> */
	public function author(): BelongsTo
	{
		return $this->belongsTo(User::class, 'author_id');
	}
}
