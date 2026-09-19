<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
	use MassPrunable;

	protected $guarded = [];

	protected $casts = [
		'users_id' => 'json:unicode',
		'data' => 'json:unicode',
	];

	/**
	 * @return Builder<static>
	 */
	public function prunable(): Builder
	{
		return static::query()->where('created_at', '<', now()->subDays(7));
	}

	public function hasLostContact(int $userId): bool
	{
		return $this->no_contact && isset($this->data['attackers'][$userId]);
	}
}
