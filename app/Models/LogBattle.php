<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property $id
 * @property $user_id
 * @property $time
 * @property $title
 * @property $log
 */
class LogBattle extends Model
{
	public $timestamps = false;
}
