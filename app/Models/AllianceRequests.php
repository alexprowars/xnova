<?php

namespace Xnova\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $a_id
 * @property int $u_id
 * @property int $time
 * @property string $request
 */
class AllianceRequests extends Model
{
	public $timestamps = false;
	public $table = 'alliance_requests';
}
