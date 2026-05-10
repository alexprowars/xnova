<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Statistic extends Model
{
	public $timestamps = false;
	protected $guarded = [];

	/** @return BelongsTo<User, $this> */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	/** @return BelongsTo<Alliance, $this> */
	public function alliance(): BelongsTo
	{
		return $this->belongsTo(Alliance::class, 'alliance_id');
	}
}
