<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AllianceMember extends Model
{
	public $timestamps = false;
	protected $table = 'alliances_members';

	public function user()
	{
		return $this->hasOne(User::class);
	}
}
