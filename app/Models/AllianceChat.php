<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AllianceChat extends Model
{
	public $timestamps = false;
	protected $table = 'alliances_chats';
	protected $guarded = false;

	protected function casts(): array
	{
		return [
			'timestamp' => 'immutable_datetime',
		];
	}

	public function alliance()
	{
		return $this->belongsTo(Alliance::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
