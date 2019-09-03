<?php

namespace Xnova\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int|null $tech_id
 * @property int $level
 */
class UsersTech extends Model
{
	public $timestamps = false;
	public $table = 'users_tech';
}