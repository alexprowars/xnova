<?php

namespace Xnova\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $planet_id
 * @property int|null $unit_id
 * @property int $amount
 * @property int $power
 */
class PlanetUnit extends Model
{
	public $timestamps = false;
}
