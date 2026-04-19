<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferalClick extends Model
{
	public $timestamps = false;
	protected $table = 'referrals_clicks';
	protected $guarded = [];

	protected $casts = [
		'date' => 'immutable_datetime',
	];

	/** @return BelongsTo<User, $this> */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id');
	}
}
