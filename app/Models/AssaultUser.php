<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssaultUser extends Model
{
	public $timestamps = false;
		protected $table = 'assaults_users';

	public function assault()
	{
		return $this->hasOne(Assault::class);
	}

	public function user()
	{
		return $this->hasOne(User::class);
	}
}
