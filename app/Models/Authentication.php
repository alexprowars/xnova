<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property $id
 * @property $user_id
 * @property $service
 * @property $service_id
 * @property $create_time
 * @property $enter_time
 */
class Authentication extends Model
{
	public $timestamps = false;
	protected $guarded = [];
}
