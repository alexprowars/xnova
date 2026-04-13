<?php

namespace App\Models;

use App\Engine\Enums\MessageType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
	use MassPrunable;
	use SoftDeletes;

	protected $table = 'messages';
	public $timestamps = false;

	protected $fillable = [
		'user_id',
		'from_id',
		'subject',
		'date',
		'message',
	];

	protected $casts = [
		'date' => 'immutable_datetime',
		'type' => MessageType::class,
		'message' => 'json:unicode',
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

	/**
	 * @return Builder<static>
	 */
	public function prunable(): Builder
	{
		return static::query()->where('date', '<', now()->subDays(14))->whereNot('type', 2);
	}
}
