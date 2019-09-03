<?php

namespace Xnova\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property $a_id
 * @property $u_id
 * @property $rank
 * @property $time
 */
class AllianceMember extends Model
{
	public $timestamps = false;
	public $table = 'alliance_members';
}