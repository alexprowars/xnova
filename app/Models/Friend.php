<?php

namespace Xnova\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property $id
 * @property $sender
 * @property $owner
 * @property $ignor
 * @property $active
 * @property $text
 */
class Friend extends Model
{
	public $timestamps = false;
}
