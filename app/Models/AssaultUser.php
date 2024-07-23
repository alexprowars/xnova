<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssaultUser extends Model
{
	public $timestamps = false;
	protected $table = 'assaults_users';
	protected $guarded = false;

	public function assault()
	{
		return $this->belongsTo(Assault::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
