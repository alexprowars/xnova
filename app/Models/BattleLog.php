<?php

namespace Xnova\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property $id
 * @property $user_id
 * @property $time
 * @property $title
 * @property $log
 */
class BattleLog extends Model
{
	public $timestamps = false;
	public $table = 'log_battle';
}