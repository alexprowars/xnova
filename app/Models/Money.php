<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $ip
 * @property int $time
 * @property string $referer
 * @property string $user_agent
 */
class Money extends Model
{
	public $timestamps = false;
}
