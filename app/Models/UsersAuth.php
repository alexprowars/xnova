<?php

namespace Xnova\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property $id
 * @property $user_id
 * @property $service
 * @property $service_id
 * @property $create_time
 * @property $enter_time
 */
class UsersAuth extends Model
{
	public $timestamps = false;
	protected $guarded = [];
	public $table = 'users_auth';
}
