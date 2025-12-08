<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogsTransfer extends Model
{
	protected $table = 'logs_transfers';
	protected $guarded = [];
	public $timestamps = false;

	protected $casts = [
		'created_at' => 'immutable_datetime',
	];

	/** @return BelongsTo<User, $this> */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	/** @return BelongsTo<User, $this> */
	public function target(): BelongsTo
	{
		return $this->belongsTo(User::class, 'target_id');
	}
}
