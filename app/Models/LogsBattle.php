<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogsBattle extends Model
{
	protected $table = 'logs_battles';
	protected $guarded = [];
	public $timestamps = false;

	protected $casts = [
		'data' => 'json:unicode',
		'created_at' => 'immutable_datetime',
	];

	/** @return BelongsTo<User, $this> */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id');
	}
}
