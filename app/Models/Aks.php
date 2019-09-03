<?php

namespace Xnova\Models;

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
class Aks extends Model
{
	public $timestamps = false;
	public $table = 'aks';
}