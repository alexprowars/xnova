<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AllianceMember extends Model
{
	protected $table = 'alliances_members';
	protected $guarded = [];

	public function alliance()
	{
		return $this->hasOne(Alliance::class);
	}

	public function user()
	{
		return $this->hasOne(User::class);
	}
}
