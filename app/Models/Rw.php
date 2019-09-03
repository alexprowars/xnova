<?php

namespace Xnova\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property $id
 * @property $id_users
 * @property $no_contact
 * @property $raport
 * @property int $time
 */
class Rw extends Model
{
	public $timestamps = false;
	public $table = 'rw';
}