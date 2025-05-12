<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referal extends Model
{
	public $timestamps = false;
	protected $guarded = false;

	/** @return BelongsTo<User, $this> */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'u_id');
	}

	/** @return BelongsTo<User, $this> */
	public function referal(): BelongsTo
	{
		return $this->belongsTo(User::class, 'r_id');
	}
}
