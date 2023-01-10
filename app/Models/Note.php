<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property $id
 * @property $user_id
 * @property $time
 * @property $priority
 * @property $title
 * @property $text
 */
class Note extends Model
{
	public $timestamps = false;
}
