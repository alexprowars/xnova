<?php

namespace Xnova\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property $id
 * @property $user_id
 * @property $time
 * @property $subject
 * @property $text
 * @property $status
 */
class Support extends Model
{
	public $timestamps = false;
	public $table = 'support';
}
