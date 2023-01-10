<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int|null $tech_id
 * @property int $level
 */
class UserTech extends Model
{
	public $timestamps = false;
}
