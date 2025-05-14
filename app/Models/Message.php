<?php

namespace App\Models;

use App\Engine\Enums\MessageType;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
	use MassPrunable;

	public $timestamps = false;

	protected $fillable = [
		'user_id',
		'from_id',
		'theme',
		'time',
		'message',
	];

	protected $casts = [
		'deleted' => 'boolean',
		'time' => 'immutable_datetime',
		'type' => MessageType::class,
	];

	/** @return BelongsTo<User, $this> */
	public function from(): BelongsTo
	{
		return $this->belongsTo(User::class, 'from_id');
	}

	/** @return BelongsTo<User, $this> */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	public function prunable()
	{
		return static::query()->where('time', '<', now()->subDays(14))->whereNot('type', 2);
	}
}
