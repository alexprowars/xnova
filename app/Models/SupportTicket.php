<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
	protected $table = 'support_tickets';
	protected $guarded = [];

	/** @return BelongsTo<User, $this> */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	/** @return HasMany<SupportMessage, $this> */
	public function messages(): HasMany
	{
		return $this->hasMany(SupportMessage::class, 'ticket_id');
	}
}
