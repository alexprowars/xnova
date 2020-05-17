<?php

namespace Xnova\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $ally_id
 * @property string $user
 * @property int $user_id
 * @property string $message
 * @property int $timestamp
 */
class AllianceChat extends Model
{
	public $timestamps = false;
	public $table = 'alliance_chat';
}
