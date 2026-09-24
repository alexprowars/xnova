<?php

namespace App\Models;

use App\Engine\Ai\StrategyType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ai extends Model
{
	protected $table = 'ai';

	protected $casts = [
		'active' => 'boolean',
		'next_run_at' => 'immutable_datetime',
		'state' => 'array',
		'strategy' => StrategyType::class,
	];

	/** @return BelongsTo<User, $this> */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}
}
