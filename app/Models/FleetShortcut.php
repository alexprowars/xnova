<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FleetShortcut extends Model
{
	protected $table = 'fleets_shortcuts';
	protected $guarded = false;

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
