<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AllianceRequest extends Model
{
	protected $table = 'alliances_requests';
	protected $guarded = false;

	public function alliance()
	{
		return $this->belongsTo(Alliance::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
