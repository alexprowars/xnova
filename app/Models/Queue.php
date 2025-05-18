<?php

namespace App\Models;

use App\Engine\Enums\QueueConstructionType;
use App\Engine\Enums\QueueType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Queue extends Model
{
	public $timestamps = false;
	protected $guarded = false;

	protected $attributes = [
		'operation' => QueueConstructionType::BUILDING,
	];

	protected $casts = [
		'date' => 'immutable_datetime',
		'date_end' => 'immutable_datetime',
		'type' => QueueType::class,
		'operation' => QueueConstructionType::class,
	];

	/** @return BelongsTo<User, $this> */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	/** @return BelongsTo<Planet, $this> */
	public function planet(): BelongsTo
	{
		return $this->belongsTo(Planet::class, 'planet_id');
	}
}
