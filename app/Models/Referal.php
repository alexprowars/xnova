<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referal extends Model
{
	protected $table = 'referals';
	public $timestamps = false;
	protected $guarded = false;

	protected $casts = [
		'date' => 'immutable_datetime',
	];

	/** @return BelongsTo<User, $this> */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	/** @return BelongsTo<User, $this> */
	public function referal(): BelongsTo
	{
		return $this->belongsTo(User::class, 'referal_id');
	}
}
