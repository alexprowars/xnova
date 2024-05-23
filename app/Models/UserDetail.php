<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
	public $timestamps = false;
	protected $guarded = [];
	public $table = 'users_details';
}
