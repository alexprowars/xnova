<?php

namespace Xnova\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $uid
 * @property int $time
 * @property int $credits
 * @property int $type
 */
class LogCredit extends Model
{
	public $timestamps = false;
}
