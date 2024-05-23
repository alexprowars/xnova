<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assault extends Model
{
	public $timestamps = false;
	protected $table = 'assaults';

	public function users()
	{
		return $this->hasMany(AssaultUser::class, 'assault_id');
	}

	public function fleets()
	{
		return $this->hasMany(Fleet::class, 'assault_id');
	}
}
