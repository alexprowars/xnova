<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
	protected $guarded = [];

	public function user()
	{
		return $this->hasOne(User::class);
	}

	public function friend()
	{
		return $this->hasOne(User::class);
	}

	public static function hasFriends(int $userId, int $friendId): bool
	{
		return self::query()->where(fn (Builder $query) =>
			$query->where(fn(Builder $query) => $query->where('user_id', $userId)->where('friend_id', $friendId))
			->orWhere(fn (Builder $query) => $query->where('user_id', $userId)->where('friend_id', $friendId)))
		->where('active', 1)
		->exists();
	}
}
