<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property $id
 * @property $user_id
 * @property $quest_id
 * @property $finish
 * @property $stage
 */
class UserQuest extends Model
{
	public $timestamps = false;
	protected $guarded = [];
}
