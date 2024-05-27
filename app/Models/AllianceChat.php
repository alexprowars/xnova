<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AllianceChat extends Model
{
	public $timestamps = false;
	protected $table = 'alliances_chats';

	protected function casts(): array
	{
		return [
			'timestamp' => 'datetime',
		];
	}
}
