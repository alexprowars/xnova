<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllianceChat extends Model
{
	protected $table = 'alliances_chats';
	public $timestamps = false;
	protected $guarded = false;

	protected $casts = [
		'date' => 'immutable_datetime',
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
