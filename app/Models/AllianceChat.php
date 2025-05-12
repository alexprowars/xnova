<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllianceChat extends Model
{
	public $timestamps = false;
	protected $table = 'alliances_chats';
	protected $guarded = false;

	protected $casts = [
		'timestamp' => 'immutable_datetime',
	];

	/** @return BelongsTo<Alliance, $this> */
	public function alliance(): BelongsTo
	{
		return $this->belongsTo(Alliance::class);
	}

	/** @return BelongsTo<User, $this> */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}
}
