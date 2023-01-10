<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string|null $name
 * @property int|null $fleet_id
 * @property int|null $galaxy
 * @property int|null $system
 * @property int|null $planet
 * @property int $planet_type
 * @property int|null $user_id
 */
class Assault extends Model
{
	public $timestamps = false;
}
