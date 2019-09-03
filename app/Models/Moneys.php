<?php

namespace Xnova\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $ip
 * @property int $time
 * @property string $referer
 * @property string $user_agent
 */
class Moneys extends Model
{
	public $timestamps = false;
	public $table = 'moneys';
}