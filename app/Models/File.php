<?php

namespace Xnova\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property $id
 * @property $src
 * @property $name
 * @property $size
 * @property $mime
 */
class File extends Model
{
	public $timestamps = false;
	public $table = 'files';
}