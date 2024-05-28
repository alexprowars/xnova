<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AllianceMember extends Model
{
	protected $table = 'alliances_members';

	public function user()
	{
		return $this->hasOne(User::class);
	}
}
