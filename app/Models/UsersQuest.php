<?php

namespace Xnova\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property $id
 * @property $user_id
 * @property $quest_id
 * @property $finish
 * @property $stage
 */
class UsersQuest extends Model
{
	public $timestamps = false;
	public $table = 'users_quests';
}