<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blocked extends Model
{
	public $timestamps = false;
	public $table = 'users_blocked';
}
