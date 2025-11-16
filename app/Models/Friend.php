<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Friend extends Model
{
	protected $guarded = [];

	protected $casts = [
		'active' => 'boolean',
		'ignore' => 'boolean',
	];

	/** @return BelongsTo<User, $this> */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	/** @return BelongsTo<User, $this> */
	public function friend(): BelongsTo
	{
		return $this->belongsTo(User::class, 'friend_id');
	}

	public static function hasFriends(User $user, User $friend): bool
	{
		return self::query()->where(fn(Builder $query) =>
			$query->where(fn(Builder $query) => $query->whereBelongsTo($user)->whereBelongsTo($friend, 'friend'))
			->orWhere(fn(Builder $query) => $query->whereBelongsTo($friend)->whereBelongsTo($user, 'friend')))
		->where('active', true)
		->exists();
	}
}
