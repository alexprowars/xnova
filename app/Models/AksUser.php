<?php

namespace Xnova\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $aks_id
 * @property int $user_id
 */
class AksUser extends Model
{
	public $timestamps = false;
	public $table = 'aks_user';
}
