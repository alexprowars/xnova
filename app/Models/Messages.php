<?php

namespace Xnova\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property $id
 * @property $user_id
 * @property $from_id
 * @property $time
 * @property $type
 * @property $deleted
 * @property $theme
 * @property $text
 * @property $from
 */
class Messages extends Model
{
	public $timestamps = false;
	public $table = 'messages';
}