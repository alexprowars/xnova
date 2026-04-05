<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportMessage extends Model
{
	/** @return BelongsTo<SupportTicket, $this> */
	public function ticket(): BelongsTo
	{
		return $this->belongsTo(SupportTicket::class, 'ticket_id');
	}

	/** @return BelongsTo<User, $this> */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id');
	}
}
